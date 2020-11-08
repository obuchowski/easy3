<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
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
        $em = $this->get('doctrine')->getManager();
        yield MenuItem::linkToCrud('Add new Source', 'fas fa-plus-circle', Feed::class)
            ->setAction('new');

        $resources = $em->getRepository(\App\Entity\Resource::class)->findAll();
        /** @var \App\Entity\Resource $resource */
        foreach ($resources as $resource) {
            yield MenuItem::section($resource->getName(), 'fas fa-shopping-basket');

            $stores = $em->getRepository(\App\Entity\Store::class)->findByResourceId($resource->getId());
            /** @var \App\Entity\Store $store */
            foreach ($stores as $store) {
                $source = [
                    MenuItem::linkToCrud('Products', 'fas fa-th-list', Product::class)->setDefaultSort(['original_id' => 'ASC']),
                    MenuItem::linkToCrud('Categories', 'fas fa-th-list', Category::class)->setDefaultSort(['id' => 'ASC']),
                ];
                yield  MenuItem::subMenu($store->getTitle(), 'fas fa-shopping-basket')->setSubItems($source);
            }

        }

        yield MenuItem::section('Feeds', 'fas fa-folder-open');
        yield MenuItem::subMenu('My Feeds', 'fas fa-shopping-basket');
        yield MenuItem::linkToCrud('New Google Feed', 'fas fa-plus-circle', Feed::class)
            ->setAction('new');
    }

    /**
     * @Route("/dashboard/generate", name="app_generate")
     * @param AdminContext $context
     */
    public function generateAction(AdminContext $context): Response
    {
        $em = $this->get('doctrine')->getManager();

        /* @TODO */
        $resource = $em->getRepository(\App\Entity\Resource::class)
            ->findByUserId($context->getUser()->getId())[0];

        $retrieveProducts = new \App\Model\RetrieveProducts($em, $resource);
        $retrieveProducts->execute();
        $this->addFlash('success', 'Products are collected!');

        /* @TODO */
        return $this->redirectToRoute('dashboard', [
            'crudAction' => 'index',
            'menuIndex' => 0,
            'crudId' => '363eef3',
            'submenuIndex' => 0]);
    }

    /**
     * @TODO apply for EA3
     */
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

    /**
     * @TODO apply for EA3
     */
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
}
