<?php
namespace SBC\AutomaticReportingBundle\Command;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use SBC\AutomaticReportingBundle\Annotation\DifferentiationColumn;
use SBC\AutomaticReportingBundle\Annotation\Report;
use SBC\AutomaticReportingBundle\Controller\Dispatcher;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReportingGenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('reporting:generate')
            ->setDescription('Send daily report on the number of entries in the application')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Building report ...');
        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        
        $annotations = $this->collect_annotations($em);

        $data = $this->prepare_data($em, $annotations);

        Dispatcher::send_report($data, $container);

        $output->writeln('Report has been successfully sent.');
    }

    /**
     * Collect annotations from entities
     * @param EntityManager $em
     * @return array
     */
    private function collect_annotations(EntityManager $em){
        $annotations = array();
        $available_entities = $this->available_entities($em);
        foreach ($available_entities as $available_entity){
            $annotation = $this->get_annotation($available_entity);
            if($annotation != null) $annotations[] = $annotation;
        }
        return $annotations;
    }

    /**
     * Get array of available entities in the project
     * @param EntityManager $em
     * @return array
     */
    private function available_entities(EntityManager $em){
        $entities = array();
        $metas = $em->getMetadataFactory()->getAllMetadata();
        foreach ($metas as $meta) {
            $entities[] = $meta;
        }
        return $entities;
    }

    /**
     * Extract annotation if exist
     * @param ClassMetadata $metadata
     * @return null|Report
     */
    private function get_annotation(ClassMetadata $metadata){
        // Get \ReflectionClass for object
        $reflectionClass = new \ReflectionClass($metadata->getName());
        // Prepare doctrine annotation reader
        $reader = new AnnotationReader();

        $annotation = null;
        $annotation = $reader->getClassAnnotation($reflectionClass, Report::class);
        if($annotation instanceof Report){
            if($annotation->dateColumn == null || $annotation->reportingName == null){
                throw new Exception('the "dateColumn" and "reportingName" in the @Report annotation are required!');
            }
            $annotation->setEntityNamespace($metadata->getName());
            $annotation->setTableName($metadata->getTableName());
        }
        return $annotation;
    }

    /**
     * Prepare entities and numbers
     * @param EntityManager $em
     * @param array $annotations
     * @return array
     */
    private function prepare_data(EntityManager $em, array $annotations){
        $data = array();

        foreach ($annotations as $annotation){
            if($annotation instanceof Report){
                if($annotation->differentiationColumn == null){// if reporting by entity itself

                    $count = $this->count_data($em, $annotation);
                    $item = array(
                        'entity' => $annotation->reportingName,
                        'count' => $count,
                    );
                    $data[] = $item;

                }else{// if reporting entity by differentiation column
                    foreach ($annotation->differentiations as $differentiation){
                        if($differentiation instanceof DifferentiationColumn){
                            $count = $this->count_data_with_differentiation(
                                $em, $annotation->getTableName(),
                                $annotation->getEntityNamespace(),
                                $annotation->dateColumn,
                                $annotation->differentiationColumn,
                                $differentiation->value
                            );

                            $item = array(
                                'entity' => $differentiation->reportingName,
                                'count' => $count,
                            );
                            $data[] = $item;
                        }
                    }
                }

            }
        }

        return $data;
    }

    /**
     * Count data by annotation and interval
     * @param EntityManager $em
     * @param Report $annotation
     * @return mixed
     */
    private function count_data(EntityManager $em, Report $annotation){
        $alias = $annotation->getTableName();
        $namespace = $annotation->getEntityNamespace();
        $column = $annotation->dateColumn;

        $start = date('y-m-d').' 00:00:00';
        $end = date('y-m-d').' 23:59:59';

        $qb = $em->createQueryBuilder();
        $qb
            ->select('count('.$alias.'.'.$annotation->dateColumn.')')
            ->from($namespace, $alias)
            ->where($alias.'.'.$column.' >= :start')
            ->andWhere($alias.'.'.$column.' <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
        ;
        return $qb->getQuery()->getSingleScalarResult();
    }

    private function count_data_with_differentiation(
        EntityManager $em,
        $alias,
        $namespace,
        $dateColumn,
        $differentiationColumn,
        $differentiationValue
    ){
        $start = date('y-m-d').' 00:00:00';
        $end = date('y-m-d').' 23:59:59';

        $qb = $em->createQueryBuilder();
        $qb
            ->select('count('.$alias.'.'.$dateColumn.')')
            ->from($namespace, $alias)
            ->where($alias.'.'.$dateColumn.' >= :start')
            ->andWhere($alias.'.'.$dateColumn.' <= :end')
            ->andWhere($alias.'.'.$differentiationColumn.' = :diffValue')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('diffValue', $differentiationValue)
        ;
        return $qb->getQuery()->getSingleScalarResult();
    }
}
