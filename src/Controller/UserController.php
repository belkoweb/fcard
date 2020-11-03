<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Session\Session;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController as BaseAdminController;

class UserController extends BaseAdminController
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

    public function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        // add dqlFilter for User entity that display elements of the logged user
        if (null === $dqlFilter) {
            $dqlFilter = sprintf('entity.id = %s', $this->getUser()->getId());
        } else {
            $dqlFilter = sprintf('entity.id = %s', $this->getUser()->getId());
        }

        return $this->get('easyadmin.query_builder')->createListQueryBuilder($this->entity, $sortField, $sortDirection, $dqlFilter);
    }

    /**
     * The method that is executed when the user performs a 'delete' action to
     * remove any entity.
     *
     * @return RedirectResponse
     *
     * @throws EntityRemoveException
     */
    public function deleteUserAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_DELETE);

        if ('DELETE' !== $this->request->getMethod()) {
            return $this->redirect($this->generateUrl('easyadmin', ['action' => 'list', 'entity' => $this->entity['name']]));
        }

        // if (...->getPseudo() == 'admin') {
        if ($this->request->attributes->get('easyadmin')['item']->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer un compte Admin !');
            return $this->redirect($this->generateUrl('easyadmin', ['action' => 'list', 'entity' => $this->entity['name']]));
        }

        $id = $this->request->query->get('id');
        $form = $this->createDeleteForm($this->entity['name'], $id);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $easyadmin = $this->request->attributes->get('easyadmin');
            $entity = $easyadmin['item'];
            
            $this->dispatch(EasyAdminEvents::PRE_REMOVE, ['entity' => $entity]);

            try {
                // delete User
                $this->em->remove($entity);
                $this->em->flush();
                // delete User session
                $session = new Session();
                $this->get('security.token_storage')->setToken(null);
                $session->invalidate();

            } catch (ForeignKeyConstraintViolationException $e) {
                throw new EntityRemoveException(['entity_name' => $this->entity['name'], 'message' => $e->getMessage()]);
            }
            $this->dispatch(EasyAdminEvents::POST_REMOVE, ['entity' => $entity]);
        }
        $this->dispatch(EasyAdminEvents::POST_DELETE);
        return $this->redirectToRoute('security_registration');
    }
}

