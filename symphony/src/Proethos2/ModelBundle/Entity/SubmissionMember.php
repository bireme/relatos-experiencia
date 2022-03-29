<?php

// This file is part of the ProEthos Software. 
// 
// Copyright 2013, PAHO. All rights reserved. You can redistribute it and/or modify
// ProEthos under the terms of the ProEthos License as published by PAHO, which
// restricts commercial use of the Software. 
// 
// ProEthos is distributed in the hope that it will be useful, but WITHOUT ANY
// WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
// PARTICULAR PURPOSE. See the ProEthos License for more details. 
// 
// You should have received a copy of the ProEthos License along with the ProEthos
// Software. If not, see
// https://github.com/bireme/proethos2/blob/master/LICENSE.txt


namespace Proethos2\ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SubmissionMember
 *
 * @ORM\Table(name="submission_member")
 * @ORM\Entity
 */
class SubmissionMember extends Base
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @var Submission
     * 
     * @ORM\ManyToOne(targetEntity="Submission") 
     * @ORM\JoinColumn(name="submission_id", referencedColumnName="id", nullable=false, onDelete="CASCADE") 
     * @Assert\NotBlank 
     */ 
    private $submission;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $academic_formation;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $professional_category;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $institution;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $responsibility;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set submission
     *
     * @param \Proethos2\ModelBundle\Entity\Submission $submission
     *
     * @return SubmissionCost
     */
    public function setSubmission(\Proethos2\ModelBundle\Entity\Submission $submission)
    {
        $this->submission = $submission;

        return $this;
    }

    /**
     * Get submission
     *
     * @return \Proethos2\ModelBundle\Entity\Submission
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return SubmissionMember
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set academicFormation
     *
     * @param string $academicFormation
     *
     * @return SubmissionMember
     */
    public function setAcademicFormation($academicFormation)
    {
        $this->academic_formation = $academicFormation;

        return $this;
    }

    /**
     * Get academicFormation
     *
     * @return string
     */
    public function getAcademicFormation()
    {
        return $this->academic_formation;
    }

    /**
     * Set professionalCategory
     *
     * @param string $professionalCategory
     *
     * @return SubmissionMember
     */
    public function setProfessionalCategory($professionalCategory)
    {
        $this->professional_category = $professionalCategory;

        return $this;
    }

    /**
     * Get professionalCategory
     *
     * @return string
     */
    public function getProfessionalCategory()
    {
        return $this->professional_category;
    }

    /**
     * Set institution
     *
     * @param string $institution
     *
     * @return SubmissionMember
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * Get institution
     *
     * @return string
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Set responsibility
     *
     * @param string $responsibility
     *
     * @return SubmissionMember
     */
    public function setResponsibility($responsibility)
    {
        $this->responsibility = $responsibility;

        return $this;
    }

    /**
     * Get responsibility
     *
     * @return string
     */
    public function getResponsibility()
    {
        return $this->responsibility;
    }
}
