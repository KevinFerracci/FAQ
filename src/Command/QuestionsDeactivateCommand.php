<?php

namespace App\Command;

use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class QuestionsDeactivateCommand extends Command
{
    protected static $defaultName = 'app:questions:deactivate';

    private $em;
    private $questionRepository;

    public function __construct(EntityManagerInterface $em, QuestionRepository $questionRepository)
    {
        parent::__construct();

        $this->em = $em;
        $this->questionRepository = $questionRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Désactive toutes les questions sans activité depuis plus de X jours (par défaut, 7 jours)')
            ->addArgument('questionId', InputArgument::OPTIONAL, 'Si vous souhaitez désactiver une question précise, indiquez son id')
            ->addOption('activate', 'a', InputOption::VALUE_NONE, 'Pour activer la question de questionId')
            ->addOption('days', 'd', InputOption::VALUE_REQUIRED, 'Nombre de jours limite d\'inactivité', 7)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $questionId = $input->getArgument('questionId');

        if ($questionId !== null) {
            $question = $this->questionRepository->find($questionId);

            if ($question === null) {
                $io->error('L\'id précisé ne correspond à aucune question en base de données');
                return 1;
            }
            
            if ($input->getOption('activate')) {
                $question->setActive(true);
            } else {
                $question->setActive(false);
            }

            $this->em->flush();

            $io->success('La question '.$questionId.' est bien (dés)activée');
            return 0;
        }

        $days = (int) $input->getOption('days');
     
        $questions = $this->questionRepository->findAll();

     
        foreach ($questions as $question) {
            $now = new \DateTime();

            if ($question->getUpdatedAt() === null) {
                
                $interval = $question->getCreatedAt()->diff($now);
            } else {
                $interval = $question->getUpdatedAt()->diff($now);
            }

            if ($interval->days > $days) {
                $question->setActive(false);
            }
        }
    
        $this->em->flush();

        $io->success('Toutes les questions sans activité depuis plus de '.$days.' jours ont été désactivées');

        return 0;
    }
}
