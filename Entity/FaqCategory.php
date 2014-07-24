<?php
/**
 * @name        FaqCategory
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
use BiberLtd\Core\CoreLocalizableEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="faq_category",
 *     options={"engine":"innodb","charset":"utf8","collate":"utf8_Turkish_ci"},
 *     indexes={@ORM\Index(name="idx_n_faq_category_date_added", columns={"date_added"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idx_u_faq_category_id", columns={"id"})}
 * )
 */
class FaqCategory extends  CoreLocalizableEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=10)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    public $date_added;

    /**
     * @ORM\Column(type="integer", length=10, nullable=false)
     */
    private $count_items;

    /**
     * @ORM\Column(type="integer", length=10, nullable=true)
     */
    private $site;

    /**
     * @ORM\OneToMany(targetEntity="BiberLtd\Core\Bundles\FAQBundle\Entity\Faq", mappedBy="faq_category")
     */
    private $faqs;

    /**
     * @ORM\OneToMany(
     *     targetEntity="BiberLtd\Core\Bundles\FAQBundle\Entity\FaqCategoryLocalization",
     *     mappedBy="faq_category"
     * )
     */
    protected $localizations;
    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

    /**
     * @name            getId()
     *                  Gets $id property.
     * .
     * @author          Murat Ünal
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          integer          $this->id
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @name                  setCount İtems()
     *                                 Sets the count_items property.
     *                                 Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $count_items
     *
     * @return          object                $this
     */
    public function setCountItems($count_items) {
        if(!$this->setModified('count_items', $count_items)->isModified()) {
            return $this;
        }
		$this->count_items = $count_items;
		return $this;
    }

    /**
     * @name            getCount İtems()
     *                           Returns the value of count_items property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->count_items
     */
    public function getCountItems() {
        return $this->count_items;
    }

    /**
     * @name                  setFaqs ()
     *                                Sets the faqs property.
     *                                Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $faqs
     *
     * @return          object                $this
     */
    public function setFaqs($faqs) {
        if(!$this->setModified('faqs', $faqs)->isModified()) {
            return $this;
        }
		$this->faqs = $faqs;
		return $this;
    }

    /**
     * @name            getFaqs ()
     *                          Returns the value of faqs property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->faqs
     */
    public function getFaqs() {
        return $this->faqs;
    }

    /**
     * @name                  setSite ()
     *                                Sets the site property.
     *                                Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $site
     *
     * @return          object                $this
     */
    public function setSite($site) {
        if(!$this->setModified('site', $site)->isModified()) {
            return $this;
        }
		$this->site = $site;
		return $this;
    }

    /**
     * @name            getSite ()
     *                          Returns the value of site property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->site
     */
    public function getSite() {
        return $this->site;
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.0                      Murat Ünal
 * 12.09.2013
 * **************************************
 * A getCountItems()
 * A getDateAdded()
 * A getFaqs()
 * A getId()
 * A getLocalizations()
 * A getSite()
 *
 * A setCountItems()
 * A setDateAdded()
 * A setFaqs()
 * A setLocalizations()
 * A setSite()
 *
 */