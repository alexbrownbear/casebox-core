<?php

namespace Casebox\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Translator;

/**
 * Class TranslationsExportCommand
 */
class TranslationsExportCommand extends ContainerAwareCommand
{
    /**
     * Configure
     */
    protected function configure()
    {
        $this
            ->setName('casebox:translations:export')
            ->setDescription('Export translations to JS.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Translator $translator */
        $translator = $this->getContainer()->get('translator');

        $locales = $translator->getFallbackLocales();

        $translations = [];

        foreach ($locales as $locale) {
            $catalog = $translator->getCatalogue($locale);

            $domains = $catalog->getDomains();
            foreach ($domains as $domain) {
                $all = $catalog->all($domain);

                if (empty($all)) {
                    continue;
                }

                foreach ($all as $key => $value) {
                    if (!empty($translations[$locale][$key]) && $translations[$locale][$key] == $value) {
                        continue;
                    }
                    $translations[$locale][$key] = $value;
                }
            }
        }

        $fs = new Filesystem();

        // Export translations
        if (!empty($translations)) {
            $public = $this
                ->getContainer()
                ->get('kernel')
                ->locateResource('@CaseboxCoreBundle/Resources/public/min/locale');

            foreach ($translations as $lang => $values) {
                $fs->dumpFile($public.'/'.$lang.'.js', "L = ".\json_encode($values));
            }

            $output->writeln('<info>[x] Translations exported.</info>');
        } else {
            $output->writeln('<error>[x] Nothing to export.</error>');
        }
    }
}