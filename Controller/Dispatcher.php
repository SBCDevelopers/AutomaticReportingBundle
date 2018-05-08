<?php
namespace SBC\AutomaticReportingBundle\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use SBC\AutomaticReportingBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Dispatcher
{

    /**
     * Prepare views and send report
     * @param array $data
     * @param ContainerInterface $container
     */
    public static function send_report(array $data, ContainerInterface $container){
        $parameters = $container->getParameter(Configuration::CONFIGURATION_NAME);
        
        $attachment = self::attachment($data, $container, $parameters);
        
        $content = $container->get('twig')->render('@AutomaticReporting/mail_body.html.twig', array(
            'app_name' => $parameters['app_name'],
        ));

        $message = \Swift_Message::newInstance()
            ->setSubject('Rapport automatique de suivie')
            ->setFrom('robot@sbc-demo.com')
            ->setTo($parameters['recipients'])
            ->setBody($content,
                'text/html'
            )
            ->attach($attachment)
        ;
        $container->get('mailer')->send($message);
    }

    /**
     * Create PDF attachment
     * @param array $data
     * @param ContainerInterface $container
     * @return \Swift_Mime_Attachment
     */
    private static function attachment(array $data, ContainerInterface $container, array $parameters){
        $view = $container->get('twig')->render('@AutomaticReporting/report.html.twig', array(
            'data' => $data,
            'app_name' => $parameters['app_name']
        ));

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $d = new Dompdf($options);
        $d->loadHtml($view);

        // Render the HTML as PDF
        $d->render();
        $fileName = 'Rapport.pdf';

        return \Swift_Attachment::newInstance($d->output(), $fileName, 'application/pdf');
    }
}