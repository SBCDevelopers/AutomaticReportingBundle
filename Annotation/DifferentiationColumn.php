<?php
namespace SBC\AutomaticReportingBundle\Annotation;

use Doctrine\Common\Annotations\Annotation as DA;

/**
 * Class DifferentiationColumn
 * @package SBC\AutomaticReportingBundle\Annotation
 * 
 * @Annotation
 */
class DifferentiationColumn
{
    /**
     * @var string
     * 
     * @DA\Required
     */
    public $value;

    /**
     * @var string
     * 
     * @DA\Required
     */
    public $reportingName;
}