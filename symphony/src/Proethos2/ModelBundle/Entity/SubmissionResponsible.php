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
use Cocur\Slugify\Slugify;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Exclude;

/**
 * SubmissionMember
 *
 * @ORM\Table(name="submission_responsible")
 * @ORM\Entity
 */
class SubmissionResponsible extends Base
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
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $filiation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $curriculum;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $orcid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @Exclude
     * @ORM\Column(type="string", length=1020, nullable=true)
     */
    private $filepath;


    public function __toString() {
        $filename = explode('_', $this->getFilename(), 2);
        return end($filename);
    }

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
     * Set filiation
     *
     * @param string $filiation
     *
     * @return SubmissionResponsible
     */
    public function setFiliation($filiation)
    {
        $this->filiation = $filiation;

        return $this;
    }

    /**
     * Get filiation
     *
     * @return string
     */
    public function getFiliation()
    {
        return $this->filiation;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return SubmissionResponsible
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return SubmissionResponsible
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set curriculum
     *
     * @param string $curriculum
     *
     * @return SubmissionResponsible
     */
    public function setCurriculum($curriculum)
    {
        $this->curriculum = $curriculum;

        return $this;
    }

    /**
     * Get curriculum
     *
     * @return string
     */
    public function getCurriculum()
    {
        return $this->curriculum;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return SubmissionResponsible
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set filepath
     *
     * @param string $filepath
     *
     * @return SubmissionResponsible
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
    }

    /**
     * Get filepath
     *
     * @return string
     */
    public function getFilepath()
    {
        return $this->filepath;
    }


    public function getDirectory() {

        $upload_directory = __DIR__.'/../../../../uploads/images';
        $submission_upload_directory = $upload_directory . "/" . str_pad($this->getSubmission()->getId(), 5, '0', STR_PAD_LEFT);

        if(!is_dir($submission_upload_directory)) {
            mkdir($submission_upload_directory);
        }

        return $submission_upload_directory;
    }

    public function setFile($file) {

        $slugify = new Slugify();
        $submission_upload_directory = $this->getDirectory();

        $filename_without_extension = str_replace("." . $file->getClientOriginalExtension(), "", $file->getClientOriginalName());
        $filename = uniqid() . '_' . $slugify->slugify($filename_without_extension) . "." . $file->getClientOriginalExtension();
        $filepath = $submission_upload_directory . "/" . $filename;
        $file = $file->move($submission_upload_directory, $filename);

        $this->setFilename($filename);
        $this->setFilepath($filepath);

        return $this;
    }

    public function getFileUri() {
        $dir = __DIR__.'/../../../..';
        $uri = "/uploads/images/" . str_pad($this->getSubmission()->getId(), 5, '0', STR_PAD_LEFT) . "/" . $this->getFilename();

        if (!file_exists($dir.$uri)) {
            foreach ( $this->getSubmission()->getProtocol()->getSubmission() as $submission ) {
                $path = "/uploads/images/" . str_pad($submission->getId(), 5, '0', STR_PAD_LEFT) . "/" . $this->getFilename();

                if (file_exists($dir.$path)) {
                    $uri = $path;
                    break;
                }
            }
        }

        return $uri;
    }

    /**
     * Set orcid
     *
     * @param string $orcid
     *
     * @return SubmissionResponsible
     */
    public function setOrcid($orcid)
    {
        $this->orcid = $orcid;

        return $this;
    }

    /**
     * Get orcid
     *
     * @return string
     */
    public function getOrcid()
    {
        return $this->orcid;
    }
}
