<?php

namespace App\Controller;

use App\Form\OrganizationType;
use App\Handler\YamlOrganizationsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class OrganizationController extends AbstractController
{
    /**
     * @var YamlOrganizationsHandler
     */
    private $organizationsHandler;

    public function __construct(YamlOrganizationsHandler $organizationsHandler)
    {
        $this->organizationsHandler = $organizationsHandler;
    }

    /**
     * @Route("/organizations")
     */
    public function list()
    {
        return $this->render('organization/list.html.twig', [
            'organizations' => $this->organizationsHandler->findAll()
        ]);
    }

    /**
     * @Route("/organization/new")
     */
    public function new(Request $request)
    {
        $form = $this->createForm(OrganizationType::class, []);

        if ($this->processForm($form, $request)) {
            return $this->redirectToRoute('app_organization_list');
        }

        return $this->render('organization/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/organizations/edit")
     */
    public function edit(Request $request)
    {
        $nameIdentifier = $request->query->get('name');
        $organization = $this->organizationsHandler->findByName($nameIdentifier);
        if (null == $organization) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(OrganizationType::class, $organization);

        if ($this->processForm($form, $request)) {
            return $this->redirectToRoute('app_organization_list');
        }

        return $this->render('organization/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/organizations/delete")
     */
    public function delete(Request $request)
    {
        if ($this->organizationsHandler->delete($request->query->get('name'))) {
            $this->addFlash('success', "L'organisation a été supprimé.");

            return $this->redirectToRoute('app_organization_list');
        }

        throw new BadRequestHttpException();
    }

    private function processForm(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->organizationsHandler->write($form->getData(), $request->query->get('name'));

            return true;
        }

        return false;
    }
}