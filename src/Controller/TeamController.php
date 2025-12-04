<?php

namespace OHMedia\TeamBundle\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;
use OHMedia\BackendBundle\Form\MultiSaveType;
use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\TeamBundle\Entity\Team;
use OHMedia\TeamBundle\Entity\TeamMember;
use OHMedia\TeamBundle\Form\TeamType;
use OHMedia\TeamBundle\Repository\TeamMemberRepository;
use OHMedia\TeamBundle\Repository\TeamRepository;
use OHMedia\TeamBundle\Security\Voter\TeamMemberVoter;
use OHMedia\TeamBundle\Security\Voter\TeamVoter;
use OHMedia\UtilityBundle\Form\DeleteType;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class TeamController extends AbstractController
{
    private const CSRF_TOKEN_REORDER = 'team_member_reorder';

    public function __construct(
        private TeamRepository $teamRepository,
        private TeamMemberRepository $teamMemberRepository,
        private Connection $connection,
        private Paginator $paginator,
        private RequestStack $requestStack
    ) {
    }

    #[Route('/teams', name: 'team_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $newTeam = new Team();

        $this->denyAccessUnlessGranted(
            TeamVoter::INDEX,
            $newTeam,
            'You cannot access the list of teams.'
        );

        $qb = $this->teamRepository->createQueryBuilder('t');
        $qb->orderBy('t.name', 'asc');

        $searchForm = $this->getSearchForm($request);

        $this->applySearch($searchForm, $qb);

        return $this->render('@OHMediaTeam/team/team_index.html.twig', [
            'pagination' => $this->paginator->paginate($qb, 20),
            'new_team' => $newTeam,
            'attributes' => $this->getAttributes(),
            'search_form' => $searchForm,
        ]);
    }

    private function getSearchForm(Request $request): FormInterface
    {
        $formBuilder = $this->container->get('form.factory')
            ->createNamedBuilder('', FormType::class, null, [
                'csrf_protection' => false,
            ]);

        $formBuilder->setMethod('GET');

        $formBuilder->add('search', SearchType::class, [
            'required' => false,
            'label' => 'Team/member name',
        ]);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        return $form;
    }

    private function applySearch(FormInterface $form, QueryBuilder $qb): void
    {
        $search = $form->get('search')->getData();

        if ($search) {
            $qb->leftJoin('t.members', 'm');

            $searchFields = [
                't.name',
                'm.first_name',
                'm.last_name',
            ];

            $searchLikes = [];
            foreach ($searchFields as $searchField) {
                $searchLikes[] = "$searchField LIKE :search";
            }

            $qb->andWhere('('.implode(' OR ', $searchLikes).')')
                ->setParameter('search', '%'.$search.'%');
        }
    }

    #[Route('/team/create', name: 'team_create', methods: ['GET', 'POST'])]
    public function create(): Response
    {
        $team = new Team();

        $this->denyAccessUnlessGranted(
            TeamVoter::CREATE,
            $team,
            'You cannot create a new team.'
        );

        $form = $this->createForm(TeamType::class, $team);

        $form->add('save', MultiSaveType::class);

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->teamRepository->save($team, true);

                $this->addFlash('notice', 'The team was created successfully.');

                return $this->redirectForm($team, $form);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaTeam/team/team_create.html.twig', [
            'form' => $form->createView(),
            'team' => $team,
        ]);
    }

    #[Route('/team/{id}', name: 'team_view', methods: ['GET'])]
    public function view(
        #[MapEntity(id: 'id')] Team $team,
    ): Response {
        $this->denyAccessUnlessGranted(
            TeamVoter::VIEW,
            $team,
            'You cannot view this team.'
        );

        $newTeamMember = new TeamMember();
        $newTeamMember->setTeam($team);

        return $this->render('@OHMediaTeam/team/team_view.html.twig', [
            'team' => $team,
            'attributes' => $this->getAttributes(),
            'new_team_member' => $newTeamMember,
            'csrf_token_name' => self::CSRF_TOKEN_REORDER,
        ]);
    }

    #[Route('/team/{id}/members/reorder', name: 'team_member_reorder_post', methods: ['POST'])]
    public function reorderPost(
        #[MapEntity(id: 'id')] Team $team,
    ): Response {
        $this->denyAccessUnlessGranted(
            TeamVoter::INDEX,
            $team,
            'You cannot reorder the members.'
        );

        $request = $this->requestStack->getCurrentRequest();

        $csrfToken = $request->request->get(self::CSRF_TOKEN_REORDER);

        if (!$this->isCsrfTokenValid(self::CSRF_TOKEN_REORDER, $csrfToken)) {
            return new JsonResponse('Invalid CSRF token.', 400);
        }

        $teamMembers = $request->request->all('order');

        $this->connection->beginTransaction();

        try {
            foreach ($teamMembers as $ordinal => $id) {
                $teamMember = $this->teamMemberRepository->find($id);

                if ($teamMember) {
                    $teamMember->setOrdinal($ordinal);

                    $this->teamMemberRepository->save($teamMember, true);
                }
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();

            return new JsonResponse('Data unable to be saved.', 400);
        }

        return new JsonResponse();
    }

    #[Route('/team/{id}/edit', name: 'team_edit', methods: ['GET', 'POST'])]
    public function edit(
        #[MapEntity(id: 'id')] Team $team,
    ): Response {
        $this->denyAccessUnlessGranted(
            TeamVoter::EDIT,
            $team,
            'You cannot edit this team.'
        );

        $form = $this->createForm(TeamType::class, $team);

        $form->add('save', MultiSaveType::class);

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->teamRepository->save($team, true);

                $this->addFlash('notice', 'The team was updated successfully.');

                return $this->redirectForm($team, $form);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaTeam/team/team_edit.html.twig', [
            'form' => $form->createView(),
            'team' => $team,
        ]);
    }

    private function redirectForm(Team $team, FormInterface $form): Response
    {
        $clickedButtonName = $form->getClickedButton()->getName() ?? null;

        if ('keep_editing' === $clickedButtonName) {
            return $this->redirectToRoute('team_edit', [
                'id' => $team->getId(),
            ]);
        } elseif ('add_another' === $clickedButtonName) {
            return $this->redirectToRoute('team_create');
        } else {
            return $this->redirectToRoute('team_view', [
                'id' => $team->getId(),
            ]);
        }
    }

    #[Route('/team/{id}/delete', name: 'team_delete', methods: ['GET', 'POST'])]
    public function delete(
        #[MapEntity(id: 'id')] Team $team,
    ): Response {
        $this->denyAccessUnlessGranted(
            TeamVoter::DELETE,
            $team,
            'You cannot delete this team.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->teamRepository->remove($team, true);

                $this->addFlash('notice', 'The team was deleted successfully.');

                return $this->redirectToRoute('team_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaTeam/team/team_delete.html.twig', [
            'form' => $form->createView(),
            'team' => $team,
        ]);
    }

    public static function getAttributes(): array
    {
        return [
            'team' => [
                'view' => TeamVoter::VIEW,
                'create' => TeamVoter::CREATE,
                'delete' => TeamVoter::DELETE,
                'edit' => TeamVoter::EDIT,
            ],
            'team_member' => [
                'create' => TeamMemberVoter::CREATE,
                'delete' => TeamMemberVoter::DELETE,
                'edit' => TeamMemberVoter::EDIT,
            ],
        ];
    }
}
