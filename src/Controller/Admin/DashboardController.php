<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("")
     */
    public function default(): Response
    {
        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Magento Feeds');
    }

    public function configureCrud(): Crud
    {
        return Crud::new();
    }

    public function configureMenuItems(): iterable
    {
        $submenu1 = [
            MenuItem::linkToCrud('menu.product.list', 'fas fa-th-list', Product::class)->setDefaultSort(['created_at' => 'DESC']),
            MenuItem::linkToCrud('menu.product.add', 'fas fa-plus-circle', Product::class)->setAction('new'),
        ];

        yield MenuItem::subMenu('menu.product', 'fas fa-shopping-basket')->setSubItems($submenu1);

        yield MenuItem::section('menu.about', 'fas fa-folder-open');
        yield MenuItem::linkToUrl('menu.about.home', 'fas fa-home', 'https://github.com/EasyCorp/EasyAdminBundle')->setLinkTarget('_blank')->setLinkRel('noreferrer');
        yield MenuItem::linkToUrl('menu.about.docs', 'fas fa-book', 'https://symfony.com/doc/current/bundles/EasyAdminBundle')->setLinkTarget('_blank')->setLinkRel('noreferrer');
        yield MenuItem::linkToUrl('menu.about.issues', 'fab fa-github', 'https://github.com/EasyCorp/EasyAdminBundle/issues')->setLinkTarget('_blank')->setLinkRel('noreferrer');
    }


    // @TODO section


    protected $userId;

    protected function initialize(Request $request)
    {
        $this->userId = $this->getUser()->getId();
        parent::initialize($request);
        if ($this->entity) {
            $filterByUser = 'entity.user = ' . $this->userId;
            foreach (['list', 'search'] as $action) {
                $this->entity[$action]['dql_filter'] .= $this->entity[$action]['dql_filter']
                    ? ' AND ' . $filterByUser
                    : $filterByUser;
            }
        }
    }

    protected function isActionAllowed($actionName)
    {
        $userCheck = true;
        if (!\in_array($actionName, ['list', 'search', 'filters', 'new', 'generate', 'batch'])) {
            $easyadmin = $this->request->attributes->get('easyadmin');
            $entity = $easyadmin['item'];
            $userCheck = $this->getUser()->getId() == $entity->getUser()->getId();
        }

        return $userCheck && parent::isActionAllowed($actionName);
    }

    public function generateAction()
    {
        $retrieveProducts = new \App\Model\RetrieveProducts($this->em, $this->getUser());
        $retrieveProducts->execute();
        $this->addFlash('success', 'Products are collected!');
        return $this->listAction();
//        return $this->redirect($this->generateUrl('easyadmin', ['action' => 'list', 'entity' => $this->entity['name']]));
    }
}
