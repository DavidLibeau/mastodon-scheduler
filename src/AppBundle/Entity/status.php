<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * status
 *
 * @ORM\Table(name="status")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\statusRepository")
 */
class status
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var account
     *
     * @ORM\Column(name="account", type="string", length=255)
     */
    private $account;

    /**
     * @var content
     *
     * @ORM\Column(name="content", type="string", length=500)
     */
    private $content;

    /**
     * @var datetime
     *
     * @ORM\Column(name="datetime", type="datetime", length=255)
     */
    private $datetime;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set account
     *
     * @param string $account
     *
     * @return status
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return status
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return content
     */
    public function getContent()
    {
        return $this->content;
    }


    /**
     * Set datetime
     *
     * @param string $datetime
     *
     * @return datetime
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Get datetime
     *
     * @return datetime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }



}
