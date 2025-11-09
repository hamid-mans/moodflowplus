<?php

namespace App\Controller;

use App\Entity\Mood;
use App\Form\MoodType;
use App\Repository\MoodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfilController extends AbstractController
{
    #[Route('/mon-profil', name: 'app_profil')]
    public function index(EntityManagerInterface $entityManager, Request $request, MoodRepository $moodRepository): Response
    {
        setlocale(LC_TIME, "fr_FR.UTF-8");
        $user = $this->getUser();

        // Déterminer le lundi et dimanche de la semaine en cours
        $today = new \DateTime();
        $monday = (clone $today)->modify('monday this week');
        $sunday = (clone $monday)->modify('sunday this week');

        // Récupérer les humeurs de l'utilisateur pour cette semaine
        $moods = $moodRepository->createQueryBuilder('m')
            ->where('m.user = :user')
            ->andWhere('m.date BETWEEN :monday AND :sunday')
            ->setParameter('user', $user)
            ->setParameter('monday', $monday->format('Y-m-d'))
            ->setParameter('sunday', $sunday->format('Y-m-d'))
            ->getQuery()
            ->getResult();

        // Indexer les humeurs par date
        $moodsByDate = [];
        foreach ($moods as $mood) {
            $moodsByDate[$mood->getDate()->format('Y-m-d')] = $mood;
        }

        // Créer le formulaire pour ajouter l'humeur du jour
        $moodForm = $this->createForm(MoodType::class, new Mood());
        $moodForm->handleRequest($request);

        if ($moodForm->isSubmitted() && $moodForm->isValid()) {
            $newMood = $moodForm->getData();
            $newMood->setUser($user);
            $newMood->setDate(new \DateTime('today'));
            $entityManager->persist($newMood);
            $entityManager->flush();

            // Rediriger pour éviter les doubles soumissions
            return $this->redirectToRoute('app_profil');
        }

        // Préparer un tableau complet de la semaine (même sans humeur)
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = (clone $monday)->modify("+$i day");
            $key = $day->format('Y-m-d');
            $weekDays[$key] = $moodsByDate[$key] ?? null;
        }

        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
            'monday' => $monday,
            'weekDays' => $weekDays, // Tableau [date => Mood|null]
            'moodForm' => $moodForm->createView(),
        ]);
    }

    #[Route('/mon-profil/mois', name: 'app_profil_mois')]
    public function month(EntityManagerInterface $entityManager, Request $request, MoodRepository $moodRepository): Response
    {
        setlocale(LC_TIME, "fr_FR.UTF-8");
        $user = $this->getUser();

        $today = new \DateTime();
        $firstDay = (clone $today)->modify('first day of this month');
        $lastDay = (clone $today)->modify('last day of this month');

        // Récupérer les humeurs de l'utilisateur pour le mois
        $moods = $moodRepository->createQueryBuilder('m')
            ->where('m.user = :user')
            ->andWhere('m.date BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $firstDay->format('Y-m-d'))
            ->setParameter('end', $lastDay->format('Y-m-d'))
            ->getQuery()
            ->getResult();

        // Indexer les humeurs par date
        $moodsByDate = [];
        foreach ($moods as $mood) {
            $moodsByDate[$mood->getDate()->format('Y-m-d')] = $mood;
        }

        // Créer le formulaire pour ajouter l'humeur du jour
        $moodForm = $this->createForm(MoodType::class, new Mood());
        $moodForm->handleRequest($request);

        if ($moodForm->isSubmitted() && $moodForm->isValid()) {
            $newMood = $moodForm->getData();
            $newMood->setUser($user);
            $newMood->setDate(new \DateTime('today'));
            $entityManager->persist($newMood);
            $entityManager->flush();

            return $this->redirectToRoute('app_profil_mois');
        }

        // Construire le tableau du mois
        $monthDays = [];
        $daysInMonth = (int) $lastDay->format('d');
        for ($i = 0; $i < $daysInMonth; $i++) {
            $day = (clone $firstDay)->modify("+$i day");
            $key = $day->format('Y-m-d');
            $monthDays[$key] = $moodsByDate[$key] ?? null;
        }

        return $this->render('profil/month.html.twig', [
            'controller_name' => 'ProfilController',
            'firstDay' => $firstDay,
            'monthDays' => $monthDays,
            'moodForm' => $moodForm->createView(),
        ]);
    }
}
