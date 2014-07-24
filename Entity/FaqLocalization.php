<?php
/**
 * @name        FaqLocalization
 * @package		BiberLtd\Core\FAQBundle
 *
 * @author		Murat Ünal
 *
 * @version     1.0.0
 * @date        12.09.2013
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */
namespace BiberLtd\Core\Bundles\FAQBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use BiberLtd\Core\CoreEntity;

/** 
 * @ORM\Entity
 * @ORM\Table(
 *     name="faq_localization",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idx_u_faq_localization", columns={"faq","language"}),
 *         @ORM\UniqueConstraint(name="idx_u_faq_localization_url_key", columns={"language","url_key"})
 *     }
 * )
 */
class FaqLocalization extends CoreEntity
{
    /** 
     * @ORM\Column(type="string", length=155, nullable=false)
     */
    private $title;

    /** 
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $url_key;

    /** 
     * @ORM\Column(type="text", nullable=false)
     */
    private $content;

    /** 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Core\Bundles\FAQBundle\Entity\Faq", inversedBy="localizations")
     * @ORM\JoinColumn(name="faq", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $faq;

    /** 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Core\Bundles\MultiLanguageSupportBundle\Entity\Language")
     * @ORM\JoinColumn(name="language", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $language;

    /**
     * @name                  setContent ()
     *                                   Sets the content property.
     *                                   Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $content
     *
     * @return          object                $this
     */
    public function setContent($content) {
        if(!$this->setModified('content', $content)->isModified()) {
            return $this;
        }
		$this->content = $content;
		return $this;
    }

    /**
     * @name            getContent ()
     *                             Returns the value of content property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->content
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @name                  setFaq ()
     *                               Sets the faq property.
     *                               Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $faq
     *
     * @return          object                $this
     */
    public function setFaq($faq) {
        if(!$this->setModified('faq', $faq)->isModified()) {
            return $this;
        }
		$this->faq = $faq;
		return $this;
    }

    /**
     * @name            getFaq ()
     *                         Returns the value of faq property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->faq
     */
    public function getFaq() {
        return $this->faq;
    }

    /**
     * @name                  setLanguage ()
     *                                    Sets the language property.
     *                                    Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $language
     *
     * @return          object                $this
     */
    public function setLanguage($language) {
        if(!$this->setModified('language', $language)->isModified()) {
            return $this;
        }
		$this->language = $language;
		return $this;
    }

    /**
     * @name            getLanguage ()
     *                              Returns the value of language property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->language
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * @name                  setTitle ()
     *                                 Sets the title property.
     *                                 Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $title
     *
     * @return          object                $this
     */
    public function setTitle($title) {
        if(!$this->setModified('title', $title)->isModified()) {
            return $this;
        }
		$this->title = $title;
		return $this;
    }

    /**
     * @name            getTitle ()
     *                           Returns the value of title property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->title
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @name                  setUrlKey ()
     *                                  Sets the url_key property.
     *                                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $url_key
     *
     * @return          object                $this
     */
    public function setUrlKey($url_key) {
        if(!$this->setModified('url_key', $url_key)->isModified()) {
            return $this;
        }
		$this->url_key = $url_key;
		return $this;
    }

    /**
     * @name            getUrlKey ()
     *                            Returns the value of url_key property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->url_key
     */
    public function getUrlKey() {
        return $this->url_key;
    }
    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

}
/**
 * Change Log:
 * **************************************
 * v1.0.0                      Murat Ünal
 * 12.09.2013
 * **************************************
 * A getContent()
 * A getFaq()
 * A getLanguage()
 * A getTitle()
 * A getUrlKey()
 *
 * A setContent()
 * A setFaq()
 * A setLanguage()
 * A setTitle()
 * A setUrlKey()
 *
 */