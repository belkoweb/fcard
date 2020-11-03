<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Card;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController as BaseAdminController;

class AdminController extends BaseAdminController
{
     /**
     * Performs a database query to get all the records related to the given
     * entity. It supports pagination and field sorting.
     *
     * @param string      $entityClass
     * @param int         $page
     * @param int         $maxPerPage
     * @param string|null $sortField
     * @param string|null $sortDirection
     * @param string|null $dqlFilter
     *
     * @return Pagerfanta The paginated query results
     */
    public function findAll($entityClass, $page = 1, $maxPerPage = 15, $sortField = null, $sortDirection = null, $dqlFilter = null)
    {
        if (null === $sortDirection || !\in_array(\strtoupper($sortDirection), ['ASC', 'DESC'])) {
            $sortDirection = 'DESC';
        }
        $queryBuilder = $this->executeDynamicMethod('create<EntityName>ListQueryBuilder', [$entityClass, $sortDirection, $sortField, $dqlFilter]);
        $this->filterQueryBuilder($queryBuilder);
        $this->dispatch(EasyAdminEvents::POST_LIST_QUERY_BUILDER, [
            'query_builder' => $queryBuilder,
            'sort_field' => $sortField,
            'sort_direction' => $sortDirection,
        ]);
        return $this->get('easyadmin.paginator')->createOrmPaginator($queryBuilder, $page, $maxPerPage);
    }

    /**
     * Creates Query Builder instance for all the records.
     *
     * @param string      $entityClass
     * @param string      $sortDirection
     * @param string|null $sortField
     * @param string|null $dqlFilter
     *
     * @return QueryBuilder The Query Builder instance
     */
    public function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        // add dqlFilter that display elements of the logged user (elements of User had to be fetch in the new controller UserController)
        if (null === $dqlFilter) {
            $dqlFilter = sprintf('entity.user = %s', $this->getUser()->getId());
        } else {
            $dqlFilter .= sprintf(' AND entity.user = %s', $this->getUser()->getId());
        }
        
        return $this->get('easyadmin.query_builder')->createListQueryBuilder($this->entity, $sortField, $sortDirection, $dqlFilter);
    }

    /**
     * This method overrides the default query builder used to search for this
     * entity. This allows to make a more complex search joining related entities.
     */
    protected function createSearchQueryBuilder($entityClass, $searchQuery, array $searchableFields, $sortField = null, $sortDirection = null, $dqlFilter = null)
    {
        /* @var EntityManager */
        $em = $this->getDoctrine()->getManagerForClass($this->entity['class']);

        // the property to search
        $name = null;
        
        if (($this->entity['class']) == 'App\Entity\Tag') {
            $name = 'name';
        } elseif (($this->entity['class']) == 'App\Entity\Card') {
            $name = 'recto';
        }

        /* @var DoctrineQueryBuilder */
        $queryBuilder = $em->createQueryBuilder()
            ->select('entity')
            ->from($this->entity['class'], 'entity')
            ->andWhere('entity.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->andWhere('LOWER(entity.' . $name . ') LIKE :query')
            ->setParameter('query', '%' . strtolower($searchQuery) . '%')
            ;
            
        if (!empty($dqlFilter)) {
            $queryBuilder->andWhere($dqlFilter);
        }

        if (null !== $sortField) {
            $queryBuilder->orderBy('entity.' . $sortField, $sortDirection ?: 'DESC');
        }

        return $queryBuilder;
    }
    
    // launched before the submission of the form that creates a new card
    public function createNewCardEntity()
    {
        $user = $this->getUser();
        $card = new Card();
        $today = new \DateTime();

        $card->setDateCreation(new \DateTime())
             ->setDatePublication($today->setTime(00, 00, 00))
             ->setUser($user);

        return $card;
    }

    // check tags to avoid duplication
    public function persistCardEntity($entity)
    {
        $this->searchTagCreated($entity);

        $this->em->persist($entity);
        $this->em->flush();
    }

    // check tags to avoid duplication
    public function updateCardEntity($entity)
    {
        $this->searchTagCreated($entity);

        $this->em->flush();
    }

    // check if the card (by the recto) is already created (to avoid duplication) // ++
    public function searchCardCreated($entity)
    {
        $recto = $entity->getRecto();
        $repo = $this->em->getRepository('App\Entity\Card');

        $cardCreated = $repo->findOneBy([
            'recto' => $recto,
            'user' => $this->getUser()
        ]);

        if ($cardCreated) {
            return true;
        }

        return false;
    }

    // check if the card updated (by the recto) is already created (to avoid duplication) // ++
    public function searchCardUpdated($entity)
    {
        $id = $entity->getId();
        $recto = $entity->getRecto();
        $repo = $this->em->getRepository('App\Entity\Card');

        // changes don't concern the recto
        $cardSameRecto = $repo->findOneBy([
            'id' => $id,
            'recto' => $recto,
            'user' => $this->getUser()
        ]);

        if ($cardSameRecto) {
            return false;
        }

        // changes concern the recto
        $cardChangeRecto = $repo->findOneBy([
            'recto' => $recto,
            'user' => $this->getUser()
        ]);

        if ($cardChangeRecto) {
            return true;
        }

        return false;
    }

    // check if the tag (by the name) is already created (to avoid duplication)
    public function searchTagCreated($entity)
    {
        $tags = $entity->getTags();

        $repo = $this->em->getRepository('App\Entity\Tag');

        foreach ($tags as $key => $value) {
            $name = $value->getName(); // ex. "maths"

            $tagCreated = $repo->findOneBy([
                'name' => $name,
                'user' => $this->getUser()
            ]); // ex. the tag of the user labelled "maths"

            if ($tagCreated) {
                $entity->removeTag($value);
                $entity->addTag($tagCreated);
            }
        };

        return $entity;
    }

    // launched before the submission of the form that creates a new tag
    public function createNewTagEntity()
    {
        $tag = new Tag();
        $tag->setUser($this->getUser());

        return $tag;
    }

    // launched when the user performs a new Card action
    public function newCardAction() // ++
    {
        $this->dispatch(EasyAdminEvents::PRE_NEW);
        $entity = $this->executeDynamicMethod('createNew<EntityName>Entity');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $easyadmin['item'] = $entity;

        $this->request->attributes->set('easyadmin', $easyadmin);
        $fields = $this->entity['new']['fields'];
        $newForm = $this->executeDynamicMethod('create<EntityName>NewForm', [$entity, $fields]);
        $newForm->handleRequest($this->request);

        if ($newForm->isSubmitted() && $newForm->isValid()) {
            // search if the submitted card doesn't already exist
            if ($this->searchCardCreated($entity)) {
                $this->addFlash('error', 'Une carte avec le même recto a déjà été crée !');
                return $this->redirect($this->request->headers->get('referer'));
            }
            $this->dispatch(EasyAdminEvents::PRE_PERSIST, ['entity' => $entity]);
            $this->executeDynamicMethod('persist<EntityName>Entity', [$entity, $newForm]);
            $this->dispatch(EasyAdminEvents::POST_PERSIST, ['entity' => $entity]);
            return $this->redirectToReferrer();
        }

        $this->dispatch(EasyAdminEvents::POST_NEW, [
            'entity_fields' => $fields,
            'form' => $newForm,
            'entity' => $entity,
        ]);

        $parameters = [
            'form' => $newForm->createView(),
            'entity_fields' => $fields,
            'entity' => $entity,
        ];

        return $this->executeDynamicMethod('render<EntityName>Template', ['new', $this->entity['templates']['new'], $parameters]);
    }

    public function editCardAction() // ++
    {
        $this->dispatch(EasyAdminEvents::PRE_EDIT);

        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];

        if ($this->request->isXmlHttpRequest() && $property = $this->request->query->get('property')) {
            $newValue = 'true' === \mb_strtolower($this->request->query->get('newValue'));
            $fieldsMetadata = $this->entity['list']['fields'];
            if (!isset($fieldsMetadata[$property]) || 'toggle' !== $fieldsMetadata[$property]['dataType']) {
                throw new \RuntimeException(\sprintf('The type of the "%s" property is not "toggle".', $property));
            }
            $this->updateEntityProperty($entity, $property, $newValue);
            // cast to integer instead of string to avoid sending empty responses for 'false'
            return new Response((int) $newValue);
        }

        $fields = $this->entity['edit']['fields'];

        $editForm = $this->executeDynamicMethod('create<EntityName>EditForm', [$entity, $fields]);
        $deleteForm = $this->createDeleteForm($this->entity['name'], $id);

        $editForm->handleRequest($this->request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            // search if the submitted card doesn't already exist
            if ($this->searchCardUpdated($entity)) {
                $this->addFlash('error', 'Une carte avec le même recto a déjà été crée !');
                return $this->redirect($this->request->headers->get('referer'));
            }
            $this->dispatch(EasyAdminEvents::PRE_UPDATE, ['entity' => $entity]);
            $this->executeDynamicMethod('update<EntityName>Entity', [$entity, $editForm]);
            $this->dispatch(EasyAdminEvents::POST_UPDATE, ['entity' => $entity]);
            return $this->redirectToReferrer();
        }

        $this->dispatch(EasyAdminEvents::POST_EDIT);

        $parameters = [
            'form' => $editForm->createView(),
            'entity_fields' => $fields,
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ];

        return $this->executeDynamicMethod('render<EntityName>Template', ['edit', $this->entity['templates']['edit'], $parameters]);
    }
}
