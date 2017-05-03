<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * user
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\userRepository")
 */
class user
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
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255)
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(name="mastodon_object", type="json_array")
     */
    private $mastodonObject;

    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", length=255)
     */
    private $accessToken;


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
     * Set name
     *
     * @param string $name
     *
     * @return user
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
     * Set mastodonObject
     *
     * @param array $mastodonObject
     *
     * @return user
     */
    public function setMastodonObject($mastodonObject)
    {
        $this->mastodonObject = $mastodonObject;

        return $this;
    }

    /**
     * Get mastodonObject
     *
     * @return array
     */
    public function getMastodonObject()
    {
        return $this->mastodonObject;
    }


    /**
     * Set accessToken
     *
     * @param string $accessToken
     *
     * @return user
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

}

