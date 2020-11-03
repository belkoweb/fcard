<?php
namespace App\EventSubscriber;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class ControllerSubscriber implements EventSubscriberInterface
{
    private $security;
    private $userRepo;
    private $manager;

    public function __construct(Security $security, UserRepository $userRepo, EntityManagerInterface $manager)
    {
        // Avoid calling getUser() in the constructor: auth may not
        // be complete yet. Instead, store the entire Security object.
        $this->security = $security;
        $this->userRepo = $userRepo;
        $this->manager = $manager;
    }
    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            KernelEvents::CONTROLLER => [
                ['processHomepage', 10]
            ],
        ];
    }
    public function processHomepage(ControllerEvent $event)
    {
        // get controller and route name
        $route = $event->getRequest()->get('_route');
        // get current user on the page
        $user = $this->security->getUser();
        // check if the user is connected, currently on the homepage and check his last connexion
        if ($user && $route == 'home') {
            $currentUser = $this->userRepo->find($user);
            // entity LastConnection related to the current user
            $userConnection = $currentUser->getLastConnection();
            $lastConnection = $userConnection->getUpdatedAt();
            $lastDay = $lastConnection->format('d-m-Y'); // ex. 27-07-2019
            $date = new \DateTime();
            $today = $date->format('d-m-Y');
            // if current user visit the homepage for the first time of the day
            if ($lastDay !== $today) {
                // the number of reviewed cards is set to 0
                $userCount = $currentUser->getDailyCount();
                $userCount->setCount(0);
                // the last day of connection is set to today
                $userConnection->setUpdatedAt(new \DateTime);
                
                $this->manager->flush();
            }
        }
    }
}