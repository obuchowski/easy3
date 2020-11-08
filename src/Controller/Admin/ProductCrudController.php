<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Product')
            ->setEntityLabelInPlural('Products')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Product (#%entity_short_id%)')
            ->setPageTitle(Crud::PAGE_NEW, 'New Product')
            ->setPaginatorPageSize(30)
            ->setSearchFields(['id', 'sku', 'name', 'price', 'status', 'visibility', 'type_id', 'options_json'])
            ->overrideTemplate('crud/index', 'admin/customizations/product_list.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        $generate = Action::new('Retrieve from Magento')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-primary')
            ->linkToRoute('app_generate');
        $actions->add(Crud::PAGE_INDEX, $generate);

        $actions
            ->disable(Action::EDIT, Action::DELETE, Action::NEW,
                Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_RETURN)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);

        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('status'))
            ->add(NumericFilter::new ('price'));
//            ->add(EntityFilter::new ('store'));
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IntegerField::new('original_id', 'ID');
        $sku = TextField::new('sku');
        $name = TextField::new('name');
        $price = MoneyField::new('price')
            ->setCurrency('USD')
            ->setCustomOption(MoneyField::OPTION_STORED_AS_CENTS, false);
        $status = BooleanField::new('status')
            ->setCustomOption('renderAsSwitch', false);
        $visibility = IntegerField::new('visibility')
            ->formatValue(static function ($value) {
                return $value === 1 ? 'Not Visible Individually'
                    : $value === 2 ? 'Catalog'
                    : $value === 3 ? 'Search'
                    : 'Catalog, Search';
            });

        $typeId = TextField::new('type_id', 'Type');
        $createdAt = DateTimeField::new('created_at');
        $updatedAt = DateTimeField::new('updated_at');
        $panel1 = FormField::addPanel('Test segment');
        $optionsJson = TextareaField::new('options_json')
            ->setCustomOption('renderAsHtml', true)
            ->formatValue(static function ($value) {
                return \str_replace(' ', '&nbsp;', $value);
            });

        if (Crud::PAGE_INDEX === $pageName)
            return [$id, $sku, $name, $visibility, $typeId, $price, $status];
        if (Crud::PAGE_DETAIL === $pageName)
            return [$id, $sku, $panel1, $name, $price, $status, $visibility, $typeId, $createdAt, $updatedAt, $optionsJson];
        if (Crud::PAGE_NEW === $pageName)
            return [$sku, $name, $price, $status, $visibility, $typeId, $createdAt, $updatedAt, $optionsJson];
        if (Crud::PAGE_EDIT === $pageName)
            return [$sku, $name, $price, $status, $visibility, $typeId, $createdAt, $updatedAt];
    }
}
