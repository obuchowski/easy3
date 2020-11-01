<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
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
            ->setEntityLabelInPlural('Product')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Product (#%entity_short_id%)')
            ->setPageTitle(Crud::PAGE_NEW, 'New Product')
            ->setSearchFields(['id', 'sku', 'name', 'price', 'status', 'visibility', 'type_id', 'options_json'])
            ->overrideTemplate('crud/index', 'admin/customizations/product_list.html.twig');
    }

    /* @TODO CHANGE field types */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('status'))
            ->add('name')
            ->add('sku')
            ->add(NumericFilter::new ('price'));
    }

    public function configureFields(string $pageName): iterable
    {
        $sku = TextField::new('sku');
        $name = TextField::new('name');
        $price = NumberField::new('price')->addCssClass('text-right');
        $status = BooleanField::new('status');
        $visibility = IntegerField::new('visibility');
        $typeId = TextField::new('type_id');
        $createdAt = DateTimeField::new('created_at');
        $updatedAt = DateTimeField::new('updated_at');
        $optionsJson = TextareaField::new('options_json');
        $user = AssociationField::new('user');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $status, $sku, $name, $visibility, $typeId, $price, $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $sku, $name, $price, $status, $visibility, $typeId, $createdAt, $updatedAt, $optionsJson, $user];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$sku, $name, $price, $status, $visibility, $typeId, $createdAt, $updatedAt, $optionsJson, $user];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$sku, $name, $price, $status, $visibility, $typeId, $createdAt, $updatedAt, $optionsJson, $user];
        }
    }
}
