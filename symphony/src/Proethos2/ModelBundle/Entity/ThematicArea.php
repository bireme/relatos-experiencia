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
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Cocur\Slugify\Slugify;

/**
 * ThematicArea
 *
 * @ORM\Table(name="list_thematic_area")
 * @ORM\Entity
 */
class ThematicArea extends Base
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
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255)
     */
    private $name;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @var Collection
     *
     * @ORM\ManyToOne(targetEntity="Collection")
     * @ORM\JoinColumn(name="collection_id", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\NotBlank
     */
    private $collection;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status = true;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=1023, nullable=true)
     */
    private $filepath;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getLocale(){
        return $this->locale;
    }

    public function getRealFilename() {
        $filename = explode('_', $this->getFilename(), 2);
        return end($filename);
    }

    public function getUploadDirectory() {
        $upload_directory = __DIR__.'/../../../../uploads/banners';

        if(!is_dir($upload_directory)) {
            mkdir($upload_directory);
        }

        return $upload_directory;
    }

    public function setFile($file) {
        $slugify = new Slugify();
        $upload_directory = $this->getUploadDirectory();

        $filename_without_extension = str_replace("." . $file->getClientOriginalExtension(), "", $file->getClientOriginalName());
        $filename = uniqid() . '_' . $slugify->slugify($filename_without_extension) . "." . $file->getClientOriginalExtension();
        $filepath = $upload_directory . "/" . $filename;
        $file = $file->move($upload_directory, $filename);

        $this->setFilename($filename);
        $this->setFilepath($filepath);

        return $this;
    }

    public function getUri() {
        return "/uploads/banners/" . $this->getFilename();
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
     * Set slug
     *
     * @param string $slug
     *
     * @return Gender
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ThematicArea
     */
    public function setName($name)
    {   
        $slugify = new Slugify();

        $this->name = $name;
        $this->slug = $slugify->slugify($name);
        
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
     * Set status
     *
     * @param boolean $status
     *
     * @return ThematicArea
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set collection
     *
     * @param \Proethos2\ModelBundle\Entity\Collection $collection
     *
     * @return ThematicArea
     */
    public function setCollection(\Proethos2\ModelBundle\Entity\Collection $collection = null)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get collection
     *
     * @return \Proethos2\ModelBundle\Entity\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return ThematicArea
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
     * @return ThematicArea
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
}
