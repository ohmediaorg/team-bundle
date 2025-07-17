<?php

namespace OHMedia\TeamBundle\Controller;

use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\TeamBundle\Entity\Team;
use OHMedia\TeamBundle\Entity\TeamMember;
use OHMedia\TeamBundle\Form\TeamMemberType;
use OHMedia\TeamBundle\Repository\TeamMemberRepository;
use OHMedia\TeamBundle\Security\Voter\TeamMemberVoter;
use OHMedia\UtilityBundle\Form\DeleteType;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class TeamMemberController extends AbstractController
{
    public function __construct(private TeamMemberRepository $teamMemberRepository)
    {
    }

    #[Route('/team/{id}/member/create', name: 'team_member_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        #[MapEntity(id: 'id')] Team $team,
    ): Response {
        $teamMember = new TeamMember();
        $teamMember->setTeam($team);

        $this->denyAccessUnlessGranted(
            TeamMemberVoter::CREATE,
            $teamMember,
            'You cannot create a new member.'
        );

        $form = $this->createForm(TeamMemberType::class, $teamMember);

        $form->add('save', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->teamMemberRepository->save($teamMember, true);

                $this->addFlash('notice', 'The member was created successfully.');

                return $this->redirectToRoute('team_view', [
                    'id' => $team->getId(),
                ]);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaTeam/team/member/team_member_create.html.twig', [
            'form' => $form->createView(),
            'team_member' => $teamMember,
            'team' => $team,
        ]);
    }

    #[Route('/team/member/{id}/edit', name: 'team_member_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(id: 'id')] TeamMember $teamMember,
    ): Response {
        $this->denyAccessUnlessGranted(
            TeamMemberVoter::EDIT,
            $teamMember,
            'You cannot edit this team member.'
        );

        $team = $teamMember->getTeam();

        $form = $this->createForm(TeamMemberType::class, $teamMember);

        $form->add('save', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->teamMemberRepository->save($teamMember, true);

                $this->addFlash('notice', 'The team member was updated successfully.');

                return $this->redirectToRoute('team_view', [
                    'id' => $team->getId(),
                ]);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaTeam/team/member/team_member_edit.html.twig', [
            'form' => $form->createView(),
            'team_member' => $teamMember,
            'team' => $team,
        ]);
    }

    #[Route('/team/member/{id}/delete', name: 'team_member_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity(id: 'id')] TeamMember $teamMember,
    ): Response {
        $this->denyAccessUnlessGranted(
            TeamMemberVoter::DELETE,
            $teamMember,
            'You cannot delete this team member.'
        );

        $team = $teamMember->getTeam();

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->teamMemberRepository->remove($teamMember, true);

                $this->addFlash('notice', 'The team member was deleted successfully.');

                return $this->redirectToRoute('team_view', [
                    'id' => $team->getId(),
                ]);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaTeam/team/member/team_member_delete.html.twig', [
            'form' => $form->createView(),
            'team_member' => $teamMember,
            'team' => $team,
        ]);
    }
}
