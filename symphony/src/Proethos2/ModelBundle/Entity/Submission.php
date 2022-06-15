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

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Exclude;

use Proethos2\CoreBundle\Util\Security;


/**
 * Submission
 *
 * @ORM\Table(name="submission")
 * @ORM\Entity(repositoryClass="Proethos2\ModelBundle\Repository\SubmissionRepository")
 */
class Submission extends Base
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
     * @var Protocol
     *
     * @ORM\ManyToOne(targetEntity="Protocol", inversedBy="submissions")
     * @ORM\JoinColumn(name="protocol_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @Assert\NotBlank
     */
    private $protocol;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_translation", type="boolean")
     */
    private $is_translation = false;

    /**
     * @var Submission
     *
     * @ORM\ManyToOne(targetEntity="Submission")
     * @ORM\JoinColumn(name="original_submission_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $original_submission;

    /**
     * @var User
     *
     * @Exclude
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @Assert\NotBlank
     */
    private $owner;

    /**
     * @var Team
     * @ORM\ManyToMany(targetEntity="User", inversedBy="users")
     * @ORM\JoinTable(name="submission_user")
     */
    private $team;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Submission", mappedBy="original_submission", cascade={"remove"})
     */
    private $translations;

    /**
     * @ORM\Column(name="number", type="integer")
     * @Assert\NotBlank
     */
    private $number;

    /******************** IDENTIFICAÇÃO DO RELATO ********************/

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", nullable=true, length=255)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=510)
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @var ThematicArea
     *
     * @ORM\ManyToMany(targetEntity="ThematicArea", inversedBy="submissions")
     * @ORM\JoinTable(name="submission_thematic_area")
     * @Assert\NotBlank
     */
    private $thematic_area;

    /**
     * @ORM\Column(type="string", length=1)
     * @Assert\NotBlank
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $other_status;

    /**
     * @var date
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $start_date;

    /**
     * @var date
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $end_date;

    /**
     * @var date
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $partial_date;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $other_date;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $keywords;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $descriptors;

    /******************** DESCRIÇÃO DO RELATO ********************/

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /** 
     * @var Country
     * 
     * @ORM\ManyToOne(targetEntity="Country") 
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", onDelete="SET NULL") 
     */ 
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $city;

    /**
     * @var PopulationGroup
     *
     * @ORM\ManyToMany(targetEntity="PopulationGroup", inversedBy="submissions")
     * @ORM\JoinTable(name="submission_population_group")
     * @Assert\NotBlank
     */
    private $population_group;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $other_population_group;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $objectives;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $resources;

    /**
     * @var Institution
     *
     * @ORM\ManyToOne(targetEntity="Institution")
     * @ORM\JoinColumn(name="institution_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $institution;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $context;

    /******************** EXPERIÊNCIA ********************/

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $main_results;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $challenges_information;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $other_results;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $lessons_learned;

    /******************** EQUIPE ********************/

    /**
     * @var SubmissionResponsible
     * @ORM\OneToMany(targetEntity="SubmissionResponsible", mappedBy="submission", cascade={"persist"})
     * @ORM\JoinTable(name="submission_responsible")
     */
    private $responsible;

    /**
     * @var SubmissionMember
     * @ORM\OneToMany(targetEntity="SubmissionMember", mappedBy="submission", cascade={"persist"})
     * @ORM\JoinTable(name="submission_member")
     */
    private $members;

    /******************** INFORMAÇÃO ADICIONAL ********************/

    /**
     * @var SubmissionUpload
     * @ORM\OneToMany(targetEntity="SubmissionUpload", mappedBy="submission", cascade={"persist"})
     * @ORM\JoinTable(name="submission_upload")
     */
    private $attachments;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $other_docs;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $other_videos;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $other_medias;

    /******************** INFORMAÇÃO ADICIONAL ********************/

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $products_information;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $related_links;

    /**
     * @var Tags
     *
     * @ORM\ManyToMany(targetEntity="Tags", inversedBy="submissions")
     * @ORM\JoinTable(name="submission_tags")
     * @Assert\NotBlank
     */
    private $tags;


    public function __construct() {

        $this->attachments = new ArrayCollection();

        // call Grandpa's constructor
        parent::__construct();

    }

    public function __clone() {

        $this->setCreated(new \Datetime());
        $this->setUpdated(new \Datetime());
        $this->setIsSended(false);

        foreach(array('attachments') as $attribute) {

            $mClone = new ArrayCollection();

            foreach ($this->$attribute as $item) {
                $itemClone = clone $item;
                $itemClone->setSubmission($this);
                $mClone->add($itemClone);
            }

            $this->$attribute = $mClone;
        }

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
     * Set protocol
     *
     * @param \Proethos2\ModelBundle\Entity\Protocol $protocol
     *
     * @return Submission
     */
    public function setProtocol(\Proethos2\ModelBundle\Entity\Protocol $protocol)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * Get protocol
     *
     * @return \Proethos2\ModelBundle\Entity\Protocol
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Set owner
     *
     * @param \Proethos2\ModelBundle\Entity\User $owner
     *
     * @return Submission
     */
    public function setOwner(\Proethos2\ModelBundle\Entity\User $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \Proethos2\ModelBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Add team
     *
     * @param \Proethos2\ModelBundle\Entity\User $team
     *
     * @return Submission
     */
    public function addTeam(\Proethos2\ModelBundle\Entity\User $team)
    {
        $this->team[] = $team;

        return $this;
    }

    /**
     * Remove team
     *
     * @param \Proethos2\ModelBundle\Entity\User $team
     */
    public function removeTeam(\Proethos2\ModelBundle\Entity\User $team)
    {
        $this->team->removeElement($team);
    }

    /**
     * Get team
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Get total team
     *
     * @return int
     */
    public function getTotalTeam()
    {
        return count($this->team) + 1;
    }

    public function isOwner(User $user)
    {
        if($this->getTeam()->contains($user)) {
            return true;
        }

        if($user == $this->getOwner()) {
            return true;
        }

        return false;
    }

    /**
     * Add attachment
     *
     * @param \Proethos2\ModelBundle\Entity\SubmissionUpload $attachment
     *
     * @return Submission
     */
    public function addAttachment(\Proethos2\ModelBundle\Entity\SubmissionUpload $attachment)
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * Remove attachment
     *
     * @param \Proethos2\ModelBundle\Entity\SubmissionUpload $attachment
     */
    public function removeAttachment(\Proethos2\ModelBundle\Entity\SubmissionUpload $attachment)
    {
        $this->attachments->removeElement($attachment);
    }

    /**
     * Get attachments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set isSended
     *
     * @param boolean $isSended
     *
     * @return Submission
     */
    public function setIsSended($isSended)
    {
        $this->is_sent = $isSended;

        return $this;
    }

    /**
     * Get isSended
     *
     * @return boolean
     */
    public function getIsSended()
    {
        return $this->is_sent;
    }

    /**
     * Set number
     *
     * @param integer $number
     *
     * @return Submission
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set isSent
     *
     * @param boolean $isSent
     *
     * @return Submission
     */
    public function setIsSent($isSent)
    {
        $this->is_sent = $isSent;

        return $this;
    }

    /**
     * Get isSent
     *
     * @return boolean
     */
    public function getIsSent()
    {
        return $this->is_sent;
    }

    /**
     * Set isTranslation
     *
     * @param boolean $isTranslation
     *
     * @return Submission
     */
    public function setIsTranslation($isTranslation)
    {
        $this->is_translation = $isTranslation;

        return $this;
    }

    /**
     * Get isTranslation
     *
     * @return boolean
     */
    public function getIsTranslation()
    {
        return $this->is_translation;
    }

    /**
     * Set originalSubmission
     *
     * @param \Proethos2\ModelBundle\Entity\Submission $originalSubmission
     *
     * @return Submission
     */
    public function setOriginalSubmission(\Proethos2\ModelBundle\Entity\Submission $originalSubmission = null)
    {
        $this->original_submission = $originalSubmission;

        return $this;
    }

    /**
     * Get originalSubmission
     *
     * @return \Proethos2\ModelBundle\Entity\Submission
     */
    public function getOriginalSubmission()
    {
        return $this->original_submission;
    }

    /**
     * can be edited?
     *
     * @return boolean
     */
    public function getCanBeEdited()
    {
        if(in_array($this->getProtocol()->getStatus(), array('D', 'R', 'C'))) {
            return true;
        } else {
            if($this->getProtocol()->getIsMigrated()) {
                return true;
            }
            return false;
        }
    }

    /**
     * Add translation
     *
     * @param \Proethos2\ModelBundle\Entity\Submission $translation
     *
     * @return Submission
     */
    public function addTranslation(\Proethos2\ModelBundle\Entity\Submission $translation)
    {
        $this->translations[] = $translation;

        return $this;
    }

    /**
     * Remove translation
     *
     * @param \Proethos2\ModelBundle\Entity\Submission $translation
     */
    public function removeTranslation(\Proethos2\ModelBundle\Entity\Submission $translation)
    {
        $this->translations->removeElement($translation);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Get total translations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTotalTranslations()
    {
        return count($this->getTranslations());
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Submission
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set otherInstitution
     *
     * @param string $otherInstitution
     *
     * @return Submission
     */
    public function setOtherInstitution($otherInstitution)
    {
        $this->other_institution = $otherInstitution;

        return $this;
    }

    /**
     * Get otherInstitution
     *
     * @return string
     */
    public function getOtherInstitution()
    {
        return $this->other_institution;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Submission
     */
    public function setStartDate($startDate)
    {
        $this->start_date = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Submission
     */
    public function setEndDate($endDate)
    {
        $this->end_date = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * Set objectives
     *
     * @param string $objectives
     *
     * @return Submission
     */
    public function setObjectives($objectives)
    {
        $this->objectives = $objectives;

        return $this;
    }

    /**
     * Get objectives
     *
     * @return string
     */
    public function getObjectives()
    {
        return $this->objectives;
    }

    /**
     * Set mainResults
     *
     * @param string $mainResults
     *
     * @return Submission
     */
    public function setMainResults($mainResults)
    {
        $this->main_results = $mainResults;

        return $this;
    }

    /**
     * Get mainResults
     *
     * @return string
     */
    public function getMainResults()
    {
        return $this->main_results;
    }

    /**
     * Set challengesInformation
     *
     * @param string $challengesInformation
     *
     * @return Submission
     */
    public function setChallengesInformation($challengesInformation)
    {
        $this->challenges_information = $challengesInformation;

        return $this;
    }

    /**
     * Get challengesInformation
     *
     * @return string
     */
    public function getChallengesInformation()
    {
        return $this->challenges_information;
    }

    /**
     * Set institution
     *
     * @param \Proethos2\ModelBundle\Entity\Institution $institution
     *
     * @return Submission
     */
    public function setInstitution(\Proethos2\ModelBundle\Entity\Institution $institution = null)
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * Get institution
     *
     * @return \Proethos2\ModelBundle\Entity\Institution
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Set country
     *
     * @param \Proethos2\ModelBundle\Entity\Country $country
     *
     * @return Submission
     */
    public function setCountry(\Proethos2\ModelBundle\Entity\Country $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \Proethos2\ModelBundle\Entity\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set language
     *
     * @param string $language
     *
     * @return Submission
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Add populationGroup
     *
     * @param \Proethos2\ModelBundle\Entity\PopulationGroup $populationGroup
     *
     * @return Submission
     */
    public function addPopulationGroup(\Proethos2\ModelBundle\Entity\PopulationGroup $populationGroup)
    {
        $this->population_group[] = $populationGroup;

        return $this;
    }

    /**
     * Remove populationGroup
     *
     * @param \Proethos2\ModelBundle\Entity\PopulationGroup $populationGroup
     */
    public function removePopulationGroup(\Proethos2\ModelBundle\Entity\PopulationGroup $populationGroup)
    {
        $this->population_group->removeElement($populationGroup);
    }

    /**
     * Get populationGroup
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPopulationGroup()
    {
        return $this->population_group;
    }

    public function getPopulationGroupList() {
        $population_groups = array();

        if ( $this->population_group ) {
            foreach($this->population_group as $population_group) {
                $population_groups[] = $population_group->getName();
            }
        }

        return $population_groups;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Submission
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set otherStatus
     *
     * @param string $otherStatus
     *
     * @return Submission
     */
    public function setOtherStatus($otherStatus)
    {
        $this->other_status = $otherStatus;

        return $this;
    }

    /**
     * Get otherStatus
     *
     * @return string
     */
    public function getOtherStatus()
    {
        return $this->other_status;
    }

    /**
     * Set partialDate
     *
     * @param \DateTime $partialDate
     *
     * @return Submission
     */
    public function setPartialDate($partialDate)
    {
        $this->partial_date = $partialDate;

        return $this;
    }

    /**
     * Get partialDate
     *
     * @return \DateTime
     */
    public function getPartialDate()
    {
        return $this->partial_date;
    }

    /**
     * Set otherDate
     *
     * @param string $otherDate
     *
     * @return Submission
     */
    public function setOtherDate($otherDate)
    {
        $this->other_date = $otherDate;

        return $this;
    }

    /**
     * Get otherDate
     *
     * @return string
     */
    public function getOtherDate()
    {
        return $this->other_date;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Submission
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Submission
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set resources
     *
     * @param string $resources
     *
     * @return Submission
     */
    public function setResources($resources)
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * Get resources
     *
     * @return string
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Set context
     *
     * @param string $context
     *
     * @return Submission
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set otherResults
     *
     * @param string $otherResults
     *
     * @return Submission
     */
    public function setOtherResults($otherResults)
    {
        $this->other_results = $otherResults;

        return $this;
    }

    /**
     * Get otherResults
     *
     * @return string
     */
    public function getOtherResults()
    {
        return $this->other_results;
    }

    /**
     * Set lessonsLearned
     *
     * @param string $lessonsLearned
     *
     * @return Submission
     */
    public function setLessonsLearned($lessonsLearned)
    {
        $this->lessons_learned = $lessonsLearned;

        return $this;
    }

    /**
     * Get lessonsLearned
     *
     * @return string
     */
    public function getLessonsLearned()
    {
        return $this->lessons_learned;
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     *
     * @return Submission
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    public function getKeywordsList() {
        $keywords = array();

        if ( $this->keywords ) {
            $keywords_list = json_decode($this->keywords);
            
            foreach($keywords_list as $keyword) {
                $keywords[] = $keyword->value;
            }
        }

        return $keywords;
    }

    /**
     * Set otherMedias
     *
     * @param string $otherMedias
     *
     * @return Submission
     */
    public function setOtherMedias($otherMedias)
    {
        $this->other_medias = $otherMedias;

        return $this;
    }

    /**
     * Get otherMedias
     *
     * @return string
     */
    public function getOtherMedias()
    {
        return $this->other_medias;
    }

    /**
     * Get getOtherMediasLinks
     *
     * @return array
     */
    public function getOtherMediasList()
    {
        $links = array();
        $_links = explode("\r\n", $this->other_medias);

        foreach ($_links as $link) {
            if (filter_var($link, FILTER_VALIDATE_URL)) {
                $links[] = $link;
            }
        }

        return $links;
    }

    /**
     * Set productsInformation
     *
     * @param string $productsInformation
     *
     * @return Submission
     */
    public function setProductsInformation($productsInformation)
    {
        $this->products_information = $productsInformation;

        return $this;
    }

    /**
     * Get productsInformation
     *
     * @return string
     */
    public function getProductsInformation()
    {
        return $this->products_information;
    }

    /**
     * Get productsInformationList
     *
     * @return array
     */
    public function getProductsInformationList()
    {
        $links = array();
        $_links = explode("\r\n", $this->products_information);

        foreach ($_links as $link) {
            if (filter_var($link, FILTER_VALIDATE_URL)) {
                $links[] = $link;
            }
        }

        return $links;
    }

    /**
     * Add thematicArea
     *
     * @param \Proethos2\ModelBundle\Entity\ThematicArea $thematicArea
     *
     * @return Submission
     */
    public function addThematicArea(\Proethos2\ModelBundle\Entity\ThematicArea $thematicArea)
    {
        $this->thematic_area[] = $thematicArea;

        return $this;
    }

    /**
     * Remove thematicArea
     *
     * @param \Proethos2\ModelBundle\Entity\ThematicArea $thematicArea
     */
    public function removeThematicArea(\Proethos2\ModelBundle\Entity\ThematicArea $thematicArea)
    {
        $this->thematic_area->removeElement($thematicArea);
    }

    /**
     * Get thematicArea
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getThematicArea()
    {
        return $this->thematic_area;
    }

    public function getThematicAreaList() {
        $thematic_area = array();

        if ( $this->thematic_area ) {
            foreach($this->thematic_area as $ta) {
                $thematic_area[] = $ta->getName();
            }
        }

        return $thematic_area;
    }

    public function getThematicAreaSlugList() {
        $thematic_area = array();

        if ( $this->thematicArea ) {
            foreach($this->thematicArea as $ta) {
                $thematic_area[] = $ta->getSlug();
            }
        }

        return $thematic_area;
    }

    /**
     * Add responsible
     *
     * @param \Proethos2\ModelBundle\Entity\SubmissionResponsible $responsible
     *
     * @return Submission
     */
    public function addResponsible(\Proethos2\ModelBundle\Entity\SubmissionResponsible $responsible)
    {
        $this->responsible[] = $responsible;

        return $this;
    }

    /**
     * Remove responsible
     *
     * @param \Proethos2\ModelBundle\Entity\SubmissionResponsible $responsible
     */
    public function removeResponsible(\Proethos2\ModelBundle\Entity\SubmissionResponsible $responsible)
    {
        $this->responsible->removeElement($responsible);
    }

    /**
     * Get responsible
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResponsible()
    {
        return $this->responsible;
    }

    /**
     * Add member
     *
     * @param \Proethos2\ModelBundle\Entity\SubmissionMember $member
     *
     * @return Submission
     */
    public function addMember(\Proethos2\ModelBundle\Entity\SubmissionMember $member)
    {
        $this->members[] = $member;

        return $this;
    }

    /**
     * Remove member
     *
     * @param \Proethos2\ModelBundle\Entity\SubmissionMember $member
     */
    public function removeMember(\Proethos2\ModelBundle\Entity\SubmissionMember $member)
    {
        $this->members->removeElement($member);
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Add tag
     *
     * @param \Proethos2\ModelBundle\Entity\Tags $tag
     *
     * @return Submission
     */
    public function addTag(\Proethos2\ModelBundle\Entity\Tags $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \Proethos2\ModelBundle\Entity\Tags $tag
     */
    public function removeTag(\Proethos2\ModelBundle\Entity\Tags $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function getTagsList() {
        $tags = array();

        if ( $this->tags ) {
            foreach($this->tags as $tag) {
                $tags[] = $tag->getName();
            }
        }

        return $tags;
    }

    /**
     * Set otherPopulationGroup
     *
     * @param string $otherPopulationGroup
     *
     * @return Submission
     */
    public function setOtherPopulationGroup($otherPopulationGroup)
    {
        $this->other_population_group = $otherPopulationGroup;

        return $this;
    }

    /**
     * Get otherPopulationGroup
     *
     * @return string
     */
    public function getOtherPopulationGroup()
    {
        return $this->other_population_group;
    }

    /**
     * Set descriptors
     *
     * @param string $descriptors
     *
     * @return Submission
     */
    public function setDescriptors($descriptors)
    {
        $this->descriptors = $descriptors;

        return $this;
    }

    /**
     * Get descriptors
     *
     * @return string
     */
    public function getDescriptors()
    {
        return $this->descriptors;
    }

    public function getDescriptorsList() {
        $descriptors = array();

        if ( $this->descriptors ) {
            $descriptors_list = json_decode($this->descriptors);
            
            foreach($descriptors_list as $descriptor) {
                $descriptors[] = $descriptor->value;
            }
        }

        return $descriptors;
    }

    /**
     * Set relatedLinks
     *
     * @param string $relatedLinks
     *
     * @return Submission
     */
    public function setRelatedLinks($relatedLinks)
    {
        $this->related_links = $relatedLinks;

        return $this;
    }

    /**
     * Get relatedLinks
     *
     * @return string
     */
    public function getRelatedLinks()
    {
        return $this->related_links;
    }

    /**
     * Get getRelatedLinksList
     *
     * @return array
     */
    public function getRelatedLinksList()
    {
        $links = array();
        $_links = explode("\r\n", $this->related_links);

        foreach ($_links as $link) {
            if (filter_var($link, FILTER_VALIDATE_URL)) {
                $links[] = $link;
            }
        }

        return $links;
    }

    /**
     * Set otherDocs
     *
     * @param string $otherDocs
     *
     * @return Submission
     */
    public function setOtherDocs($otherDocs)
    {
        $this->other_docs = $otherDocs;

        return $this;
    }

    /**
     * Get otherDocs
     *
     * @return string
     */
    public function getOtherDocs()
    {
        return $this->other_docs;
    }

    /**
     * Get getOtherDocsLinks
     *
     * @return array
     */
    public function getOtherDocsList()
    {
        $links = array();
        $_links = explode("\r\n", $this->other_docs);

        foreach ($_links as $link) {
            if (filter_var($link, FILTER_VALIDATE_URL)) {
                $links[] = $link;
            }
        }

        return $links;
    }

    /**
     * Set otherVideos
     *
     * @param string $otherVideos
     *
     * @return Submission
     */
    public function setOtherVideos($otherVideos)
    {
        $this->other_videos = $otherVideos;

        return $this;
    }

    /**
     * Get otherVideos
     *
     * @return string
     */
    public function getOtherVideos()
    {
        return $this->other_videos;
    }

    /**
     * Get getOtherVideosLinks
     *
     * @return array
     */
    public function getOtherVideosList()
    {
        $links = array();
        $_links = explode("\r\n", $this->other_videos);

        foreach ($_links as $link) {
            if (filter_var($link, FILTER_VALIDATE_URL)) {
                $links[] = $link;
            }
        }

        return $links;
    }

    /**
     * Set region
     *
     * @param string $region
     *
     * @return Submission
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Submission
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }
}
