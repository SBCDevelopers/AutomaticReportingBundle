<?php
namespace SBC\AutomaticReportingBundle\Annotation;

use Doctrine\Common\Annotations\Annotation as DA;

/**
 * Class Report
 * @package SBC\AutomaticReportingBundle\Annotation
 *
 * @Annotation
 * @DA\Target("CLASS")
 */
final class Report
{
    /**
     * The name of creation date that will be used to check the creation date of the entity
     * @var string
     *
     * @DA\Required
     */
    public $dateColumn;

    /**
     * Name of the entity that will be used when sending the report
     * @var string
     *
     * @DA\Required
     */
    public $reportingName;

    /**
     * Namespace of the entity
     * @var string
     */
    private $entityNamespace;

    /**
     * Table name of the entity
     * @var string
     */
    private $tableName;

    /**
     * @return string
     */
    public function getEntityNamespace()
    {
        return $this->entityNamespace;
    }

    /**
     * @param string $entityNamespace
     * @return Report
     */
    public function setEntityNamespace($entityNamespace)
    {
        $this->entityNamespace = $entityNamespace;
        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     * @return Report
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }
    
    
}