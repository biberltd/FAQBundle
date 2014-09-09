<?php
/**
 * @name        Faq
 * @package		BiberLtd\Bundle\CoreBundle\FAQBundle
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
namespace BiberLtd\Bundle\FAQBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use BiberLtd\Bundle\CoreBundle\CoreLocalizableEntity;

/** 
 * @ORM\Entity
 * @ORM\Table(
 *     name="faq",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={
 *         @ORM\Index(name="idx_n_faq_date_added", columns={"date_added"}),
 *         @ORM\Index(name="idx_n_faq_date_updated", columns={"date_updated"})
 *     },
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idx_u_faq_id", columns={"id"})}
 * )
 */
class Faq extends CoreLocalizableEntity
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
     * @ORM\Column(type="datetime", unique=true, nullable=false)
     */
    public $date_updated;

    /** 
     * @ORM\OneToMany(targetEntity="BiberLtd\Bundle\FAQBundle\Entity\FaqLocalization", mappedBy="faq")
     */
    protected $localizations;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\FAQBundle\Entity\FaqCategory", inversedBy="faqs")
     * @ORM\JoinColumn(name="category", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $faq_category;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     */
    private $site;
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
     * @name                  setFaqCategory ()
     *                                       Sets the faq_category property.
     *                                       Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $faq_category
     *
     * @return          object                $this
     */
    public function setFaqCategory($faq_category) {
        if(!$this->setModified('faq_category', $faq_category)->isModified()) {
            return $this;
        }
		$this->faq_category = $faq_category;
		return $this;
    }

    /**
     * @name            getFaqCategory ()
     *                                 Returns the value of faq_category property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->faq_category
     */
    public function getFaqCategory() {
        return $this->faq_category;
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
 * A getDateAdded()
 * A getDateUpdated()
 * A getFaqCategory()
 * A getId()
 * A getLocalizations()
 * A getSite()
 *
 * A setDateAdded()
 * A setDateUpdated()
 * A setFaqCategory()
 * A setLocalizations()
 * A setSite()
 *
 */