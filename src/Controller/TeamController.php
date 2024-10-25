<?php

namespace OHMedia\TeamBundle\Controller;

use Doctrine\DBAL\Connection;
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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function index(): Response
    {
        $newTeam = new Team();

        $this->denyAccessUnlessGranted(
            TeamVoter::INDEX,
            $newTeam,
            'You cannot access the list of teams.'
        );

        $qb = $this->teamRepository->createQueryBuilder('t');
        $qb->orderBy('t.name', 'asc');

        return $this->render('@OHMediaTeam/team/team_index.html.twig', [
            'pagination' => $this->paginator->paginate($qb, 20),
            'new_team' => $newTeam,
            'attributes' => $this->getAttributes(),
        ]);
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

        $form->add('submit', SubmitType::class);

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->teamRepository->save($team, true);

                $this->addFlash('notice', 'The team was created successfully.');

                return $this->redirectToRoute('team_view', [
                    'id' => $team->getId(),
                ]);
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

        $form->add('submit', SubmitType::class);

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->teamRepository->save($team, true);

                $this->addFlash('notice', 'The team was updated successfully.');

                return $this->redirectToRoute('team_view', [
                    'id' => $team->getId(),
                ]);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaTeam/team/team_edit.html.twig', [
            'form' => $form->createView(),
            'team' => $team,
        ]);
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
