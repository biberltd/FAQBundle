<?php

/**
 * FAQModel Class
 *
 * This class acts as a database proxy model for FAQBundle functionalities.
 *
 * @vendor      BiberLtd
 * @package	Core\Bundles\FAQBundle
 * @subpackage	Services
 * @name	FAQModel
 *
 * @author      Said Imamoglu
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.1
 * @date        25.12.2013
 *
 * =============================================================================================================
 * !! INSTRUCTIONS ON IMPORTANT ASPECTS OF MODEL METHODS !!!
 *
 * Each model function must return a $response ARRAY.
 * The array must contain the following keys and corresponding values.
 *
 * $response = array(
 *              'result'    =>   An array that contains the following keys:
 *                               'set'         Actual result set returned from ORM or null
 *                               'total_rows'  0 or number of total rows
 *                               'last_insert_id' The id of the item that is added last (if insert action)
 *              'error'     =>   true if there is an error; false if there is none.
 *              'code'      =>   null or a semantic and short English string that defines the error concanated
 *                               with dots, prefixed with err and the initials of the name of model class.
 *                               EXAMPLE: err.amm.action.not.found success messages have a prefix called scc..
 *
 *                               NOTE: DO NOT FORGET TO ADD AN ENTRY FOR ERROR CODE IN BUNDLE'S
 *                               RESOURCES/TRANSLATIONS FOLDER FOR EACH LANGUAGE.
 */

namespace BiberLtd\Core\Bundles\FAQBundle\Services;

/** Extends CoreModel */
use BiberLtd\Core\CoreModel;
/** Entities to be used */
use BiberLtd\Core\Bundles\FAQBundle\Entity as BundleEntity;
/** Helper Models */
use BiberLtd\Core\Bundles\FAQBundle\Services as SMMService;
use BiberLtd\Core\Bundles\MultiLanguageSupportBundle\Services as MLSService;
/** Core Service */
use BiberLtd\Core\Services as CoreServices;

class FAQModel extends CoreModel {
    /**
     * @name            __construct()
     *                  Constructor.
     *
     * @author          Said Imamoglu
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           object          $kernel
     * @param           string          $db_connection  Database connection key as set in app/config.yml
     * @param           string          $orm            ORM that is used.
     */

    /** @var $by_opitons handles by options */
    public $by_opts = array('entity', 'id', 'code', 'url_key', 'post');

    /* @var $type must be [i=>image,s=>software,v=>video,f=>flash,d=>document,p=>package] */
    public $type_opts = array('m' => 'media', 'i' => 'image', 'a' => 'audio', 'v' => 'video', 'f' => 'flash', 'd' => 'document', 'p' => 'package', 's' => 'software');
    public $eq_opts = array('after', 'before', 'between', 'on', 'more', 'less', 'eq');

    public function __construct($kernel, $db_connection = 'default', $orm = 'doctrine') {
        parent::__construct($kernel, $db_connection, $orm);

        /**
         * Register entity names for easy reference.
         */
        $this->entity = array(
            'faq' => array('name' => 'FileManagementBundle:File', 'alias' => 'f'),
            'faq_category' => array('name' => 'FAQBundle:Gallery', 'alias' => 'fc'),
            'faq_localization' => array('name' => 'FAQBundle:GalleryLocalization', 'alias' => 'fl'),
            'faq_category_localization' => array('name' => 'FAQBundle:GalleryMedia', 'alias' => 'fcl'),
        );
    }

    /**
     * @name            __destruct()
     *                  Destructor.
     *
     * @author          Said Imamoglu
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     */
    public function __destruct() {
        foreach ($this as $property => $value) {
            $this->$property = null;
        }
    }

    /**
     * @name 		deleteFaq()
     * Deletes an existing item from database.
     *
     * @since		1.0.0
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->deleteFaqs()
     *
     * @param           mixed           $item           Entity, id or url key of item
     * @param           string          $by
     *
     * @return          mixed           $response
     */
    public function deleteFaq($item, $by = 'entity') {
        return $this->deleteFaqs(array($item), $by);
    }

    /**
     * @name            deleteFaqs()
     * Deletes provided items from database.
     *
     * @since		1.0.0
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     *
     * @param           array           $collection     Collection of Faq entities, ids, or codes or url keys
     * @param           string          $by             Accepts the following options: entity, id, code, url_key
     *
     * @return          array           $response
     */
    public function deleteFaqs($collection, $by = 'entity') {
        $this->resetResponse();
        $by_opts = array('entity', 'id', 'url_key');
        if (!in_array($by, $by_opts)) {
            return $this->createException('InvalidParameterValueException', 'err.invalid.parameter.collection', implode(',', $by_opts));
        }
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'err.invalid.parameter.collection', 'Array');
        }
        $entries = array();
        /** Loop through items and collect values. */
        $delete_count = 0;
        foreach ($collection as $item) {
            $value = '';
            if (is_object($item)) {
                if (!$item instanceof BundleEntity\Faq) {
                    return $this->createException('InvalidParameterException', 'err.invalid.parameter.collection', 'BundleEntity\Faq');
                }
                $this->em->remove($item);
                $delete_count++;
            } else if (is_numeric($item) || is_string($item)) {
                $value = $item;
            } else {
                /** If array values are not numeric nor object */
                return $this->createException('InvalidParameterException', 'err.invalid.parameter.collection', 'integer, string, or Module entity');
            }
            if (!empty($value) && $value != '') {
                $entries[] = $value;
            }
        }
        /**
         * Control if there is any entity ids in collection.
         */
        if (count($entries) < 1) {
            return $this->createException('InvalidParameterException', 'err.invalid.parameter.collection', 'Array');
        }
        $join_needed = false;
        /**
         * Prepare query string.
         */
        switch ($by) {
            case 'entity':
                /** Flush to delete all persisting objects */
                $this->em->flush();
                /**
                 * Prepare & Return Response
                 */
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => null,
                        'total_rows' => $delete_count,
                        'last_insert_id' => null,
                    ),
                    'error' => false,
                    'code' => 'scc.db.delete.done',
                );
                return $this->response;
            case 'id':
                $values = implode(',', $entries);
                break;
            /** Requires JOIN */
            case 'url_key':
                $join_needed = true;
                $values = implode('\',\'', $entries);
                $values = '\'' . $values . '\'';
                break;
        }
        if ($join_needed) {
            $q_str = 'DELETE ' . $this->entity['faq']['alias']
                    . ' FROM ' . $this->entity['faq_localization']['name'] . ' ' . $this->entity['faq_localization']['alias']
                    . ' JOIN ' . $this->entity['faq_localization']['name'] . ' ' . $this->entity['faq_localization']['alias']
                    . ' WHERE ' . $this->entity['faq_localization']['alias'] . '.' . $by . ' IN(:values)';
        } else {
            $q_str = 'DELETE ' . $this->entity['faq']['alias']
                    . ' FROM ' . $this->entity['faq']['name'] . ' ' . $this->entity['faq']['alias']
                    . ' WHERE ' . $this->entity['faq']['alias'] . '.' . $by . ' IN(:values)';
        }
        /**
         * Create query object.
         */
        $query = $this->em->createQuery($q_str);
        $query->setParameter('values', $entries);
        /**
         * Free memory.
         */
        unset($values);
        /**
         * 6. Run query
         */
        $query->getResult();
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $entries,
                'total_rows' => count($entries),
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.delete.done',
        );
        return $this->response;
    }

    /**
     * @name            listFaqs()
     * List items of a given collection.
     *
     * @since		1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     *
     * @use             $this->resetResponse()
     * @use             $this->createException()
     * @use             $this->prepare_where()
     * @use             $this->createQuery()
     * @use             $this->getResult()
     * 
     * @throws          InvalidSortOrderException
     * @throws          InvalidLimitException
     * 
     *
     * @param           mixed           $filter                Multi dimensional array
     * @param           array           $sortorder              Array
     *                                                              'column'    => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     * @param           string           $query_str             If a custom query string needs to be defined.
     *
     * @return          array           $response
     */
    public function listFaqs($filter = null, $sortorder = null, $limit = null, $query_str = null) {
        $this->resetResponse();
        if (!is_array($sortorder) && !is_null($sortorder)) {
            return $this->createException('InvalidSortOrderException', '', 'err.invalid.parameter.sortorder');
        }

        /**
         * Add filter check to below to set join_needed to true
         */
        $order_str = '';
        $where_str = '';
        $group_str = '';
        $filter_str = '';


        /**
         * Start creating the query
         *
         * Note that if no custom select query is provided we will use the below query as a start
         */
        $localizable = true;
        if (is_null($query_str)) {
            if ($localizable) {
                $query_str = 'SELECT ' . $this->entity['faq_localization']['alias']
                        . ' FROM ' . $this->entity['faq_localization']['name'] . ' ' . $this->entity['faq_localization']['alias']
                        . ' JOIN ' . $this->entity['faq_localization']['alias'] . '.COLUMN ' . $this->entity['faq']['alias'];
            } else {
                $query_str = 'SELECT ' . $this->entity['faq']['alias']
                        . ' FROM ' . $this->entity['faq']['name'] . ' ' . $this->entity['faq']['alias'];
            }
        }
        /*
         * Prepare ORDER BY section of query
         */
        if (!is_null($sortorder)) {
            foreach ($sortorder as $column => $direction) {
                switch ($column) {
                    case 'id':
                    case 'name':
                    case 'url_key':
                        break;
                }
                $order_str .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }

        /*
         * Prepare WHERE section of query
         */

        if (!is_null($filter)) {
            $filter_str = $this->prepare_where($filter);
            $where_str = ' WHERE ' . $filter_str;
        }



        $query_str .= $where_str . $group_str . $order_str;


        $query = $this->em->createQuery($query_str);

        /*
         * Prepare LIMIT section of query
         */

        if (!is_null($limit) && is_numeric($limit)) {
            /*
             * if limit is set
             */
            if (isset($limit['start']) && isset($limit['count'])) {
                $query = $this->addLimit($query, $limit);
            } else {
                $this->createException('InvalidLimitException', '', 'err.invalid.limit');
            }
        }
        //print_r($query->getSql()); die;
        /*
         * Prepare and Return Response
         */

        $files = $query->getResult();


        $total_rows = count($files);
        if ($total_rows < 1) {
            $this->response['error'] = true;
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }

        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $files,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );

        return $this->response;
    }

    /**
     * @name 		getFaq()
     * Returns details of a gallery.
     *
     * @since		1.0.0
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     * @use             $this->listFaqs()
     *
     * @param           mixed           $item               id, url_key
     * @param           string          $by                 entity, id, url_key
     *
     * @return          mixed           $response
     */
    public function getFaq($item, $by = 'id') {
        $this->resetResponse();
        $by_opts = array('id', 'url_key');
        if (!in_array($by, $by_opts)) {
            return $this->createException('InvalidParameterValueException', implode(',', $by_opts), 'err.invalid.parameter.by');
        }
        if (!is_object($item) && !is_numeric($item) && !is_string($item)) {
            return $this->createException('InvalidParameterException', 'Faq', 'err.invalid.parameter');
        }
        if (is_object($item)) {
            if (!$item instanceof BundleEntity\Faq) {
                return $this->createException('InvalidParameterException', 'Faq', 'err.invalid.parameter');
            }
            /**
             * Prepare & Return Response
             */
            $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => $item,
                    'total_rows' => 1,
                    'last_insert_id' => null,
                ),
                'error' => false,
                'code' => 'scc.db.entry.exist',
            );
            return $this->response;
        }
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['faq_localization']['alias'] . '.' . $by, 'comparison' => '=', 'value' => $item),
                )
            )
        );

        $response = $this->listFaqs($filter, null, array('start' => 0, 'count' => 1));
        if ($response['error']) {
            return $response;
        }
        $collection = $response['result']['set'];
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $collection[0],
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 		doesFaqExist()
     * Checks if entry exists in database.
     *
     * @since		1.0.0
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->getFaq()
     *
     * @param           mixed           $item           id, url_key
     * @param           string          $by             id, url_key
     *
     * @param           bool            $bypass         If set to true does not return response but only the result.
     *
     * @return          mixed           $response
     */
    public function doesFaqExist($item, $by = 'id', $bypass = false) {
        $this->resetResponse();
        $exist = false;

        $response = $this->getFaq($item, $by);

        if (!$response['error'] && $response['result']['total_rows'] > 0) {
            $exist = $response['result']['set'];
            $error = false;
        } else {
            $exist = false;
            $error = true;
        }

        if ($bypass) {
            return $exist;
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $exist,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => $error,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 		insertFaq()
     * Inserts one or more item into database.
     *
     * @since		1.0.1
     * @version         1.0.3
     * @author          Said Imamoglu
     *
     * @use             $this->insertFiles()
     *
     * @param           array           $item        Collection of entities or post data.
     *
     * @return          array           $response
     */
    public function insertFaq($item, $by = 'post') {
        $this->resetResponse();
        return $this->insertFaqs($item);
    }

    /**
     * @name            insertFaqs()
     * Inserts one or more items into database.
     *
     * @since           1.0.1
     * @version         1.0.3
     * @author          Said Imamoglu
     *
     * @use             $this->createException()
     *
     * @throws          InvalidParameterException
     * @throws          InvalidMethodException
     *
     * @param           array           $collection        Collection of entities or post data.
     * @param           string          $by                entity, post
     *
     * @return          array           $response
     */
    public function insertFaqs($collection, $by = 'post') {
        /* Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'array() or Integer', 'err.invalid.parameter.collection');
        }

        if (!in_array($by, $this->by_opts)) {
            return $this->createException('InvalidParameterException', implode(',', $this->by_opts), 'err.invalid.parameter.by.collection');
        }

        if ($by == 'entity') {
            $sub_response = $this->insert_entities($collection, 'BiberLtd\\Core\\Bundles\\FAQBundle\\Entity\\Faq');
            /**
             * If there are items that cannot be deleted in the collection then $sub_Response['process']
             * will be equal to continue and we need to continue process; otherwise we can return response.
             */
            if ($sub_response['process'] == 'stop') {
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => $sub_response['entries']['valid'],
                        'total_rows' => $sub_response['item_count'],
                        'last_insert_id' => null,
                    ),
                    'error' => false,
                    'code' => 'scc.db.insert.done.',
                );

                return $this->response;
            } else {
                $collection = $sub_response['entries']['invalid'];
            }
        } elseif ($by == 'post') {
            $l_collection = array();
            $to_insert = 0;
            foreach ($collection as $item) {
                $localizations = array();
                if (isset($item['localizations'])) {
                    $localizations = $item['localizations'];
                    unset($item['localizations']);
                }
                $site = '';
                if (isset($item['site'])) {
                    $site = $item['site'];
                    unset($item['site']);
                }
                $entity = new BundleEntity\Faq();
                foreach ($item as $column => $value) {
                    $method = 'set_' . $column;
                    if (method_exists($entity, $method)) {
                        $entity->$method($value);
                    }
                }
                /** HANDLE FOREIGN DATA :: LOCALIZATIONS */
                if (count($localizations) > 0) {
                    $l_collection[] = $localizations;
                }

                $this->insert_entities(array($entity), 'BiberLtd\\Core\\Bundles\\FAQBundle\\Entity\\Faq');

                $entity_localizations = array();
                foreach ($l_collection as $localization) {
                    if ($localization instanceof BundleEntity\FaqLocalization) {
                        $entity_localizations[] = $localization;
                    } else {
                        $localization_entity = new BundleEntity\FaqLocalization;
                        $localization_entity->setProduct($entity);
                        foreach ($localization as $key => $value) {
                            $l_method = 'set_' . $key;
                            switch ($key) {
                                case 'language';
                                    $MLSModel = new MLSService\MultiLanguageSupportModel($this->kernel, $this->db_connection, $this->orm);
                                    $response = $MLSModel->getLanguage($value, 'id');
                                    if ($response['error']) {
                                        new CoreExceptions\InvalidLanguageException($this->kernel, $value);
                                        break;
                                    }
                                    $language = $response['result']['set'];
                                    $localization_entity->setLanguage($language);
                                    unset($response, $MLSModel);
                                    break;
                                default:
                                    if (method_exists($localization_entity, $l_method)) {
                                        $localization_entity->$l_method($value);
                                    } else {
                                        new CoreExceptions\InvalidMethodException($this->kernel, $method);
                                    }
                                    break;
                            }
                            $entity_localizations[] = $localization_entity;
                        }
                    }
                }
                $this->insert_entities($l_collection, 'BiberLtd\Core\\Bundles\\FAQBundle\\Entity\\FaqLocalization');
                /**
                 * ????? DO we really need this?
                 *
                 * Test! Also check if you can make use of insert_localizations functions but of course this will require
                 * dependency on MLSModel.
                 */
                $entity->setLocalizations($entity_localizations);
                $this->em->persist($entity);
                $to_insert++;
                /** Free some memory */
                unset($entity_localizations);
            }
            $this->em->flush();
            $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => $collection,
                    'total_rows' => count($collection),
                    'last_insert_id' => $entity->getId(),
                ),
                'error' => false,
                'code' => 'scc.db.insert.done',
            );

            return $this->response;
        }
    }

    /*
     * @name            updateFaq()
     * Updates single item. The item must be either a post data (array) or an entity
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->resetResponse()
     * @use             $this->updateFaqs()
     * 
     * @param           mixed   $item     Entity or Entity id of a folder
     * 
     * @return          array   $response
     * 
     */

    public function updateFaq($item) {
        $this->resetResponse();
        return $this->updateFaqs(array($item));
    }

    /*
     * @name            updateFaqs()
     * Updates one or more item details in database.
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->update_entities()
     * @use             $this->createException()
     * @use             $this->listFaqs()
     * 
     * 
     * @throws          InvalidParameterException
     * 
     * @param           array   $collection     Collection of item's entities or array of entity details.
     * @param           array   $by             entity or post
     * 
     * @return          array   $response
     * 
     */

    public function updateFaqs($collection, $by = 'post') {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $by_opts = array('entity', 'post');
        if (!in_array($by, $by_opts)) {
            return $this->createException('InvalidParameterException', implode(',', $by_opts), 'err.invalid.parameter.by');
        }
        if ($by == 'entity') {
            $sub_response = $this->update_entities($collection, 'BiberLtd\\Core\\Bundles\\FAQBundle\\Entity\\Faq');
            /**
             * If there are items that cannot be deleted in the collection then $sub_Response['process']
             * will be equal to continue and we need to continue process; otherwise we can return response.
             */
            if ($sub_response['process'] == 'stop') {
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => $sub_response['entries']['valid'],
                        'total_rows' => $sub_response['item_count'],
                        'last_insert_id' => null,
                    ),
                    'error' => false,
                    'code' => 'scc.db.delete.done',
                );
                return $this->response;
            } else {
                $collection = $sub_response['entries']['invalid'];
            }
        }
        /**
         * If by post
         */
        $to_update = array();
        $count = 0;
        $collection_by_id = array();
        foreach ($collection as $item) {
            if (!isset($item['id'])) {
                unset($collection[$count]);
            }
            $to_update[] = $item['id'];
            $collection_by_id[$item['id']] = $item;
            $count++;
        }
        unset($collection);
        $filter = array(
            array(
                'glue' => 'and',
                'condition' => array(
                    array(
                        'glue' => 'and',
                        'condition' => array('column' => $this->entity['faq']['alias'] . '.id', 'comparison' => 'in', 'value' => $to_update),
                    )
                )
            )
        );
        $response = $this->listFaqs($filter);
        if ($response['error']) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $entities = $response['result']['set'];
        foreach ($entities as $entity) {
            $data = $collection_by_id[$entity->getId()];
            /** Prepare foreign key data for process */
            $localizations = array();
            if (isset($data['localizations'])) {
                $localizations = $data['localizations'];
            }
            unset($data['localizations']);
            $site = '';
            if (isset($data['site'])) {
                $site = $data['site'];
            }
            unset($data['site']);

            foreach ($data as $column => $value) {
                $method_set = 'set_' . $column;
                $method_get = 'get_' . $column;
                /**
                 * Set the value only if there is a corresponding value in collection and if that value is different
                 * from the one set in database
                 */
                if (isset($collection_by_id[$entity->getId()][$column]) && $collection_by_id[$entity->getId()][$column] != $entity->$method_get()) {
                    $entity->$method_set($value);
                }
                /** HANDLE FOREIGN DATA :: LOCALIZATIONS */
                $l_collection = array();
                foreach ($localizations as $lang => $localization) {
                    $MLSModel = new MLSService\MultiLanguageSupportModel($this->kernel, $this->db_connection, $this->orm);
                    $response = $MLSModel->getLanguage($lang, 'iso_code');
                    if ($response['error']) {
                        new CoreExceptions\InvalidLanguageException($this->kernel, $value);
                        break;
                    }
                    $language = $response['result']['set'];
                    $translation_exists = true;
                    $response = $this->getProductLocalization($entity, $language);
                    if ($response['error']) {
                        $localization_entity = new BundleEntity\FaqLocalization();
                        $translation_exists = false;
                    } else {
                        $localization_entity = $response['result']['set'];
                    }
                    foreach ($localization as $key => $value) {
                        $l_method = 'set_' . $key;
                        switch ($key) {
                            case 'product':
                                $localization_entity->setFaq($entity);
                                break;
                            case 'language';
                                $language = $response['result']['set'];
                                $localization_entity->setLanguage($language);
                                unset($language, $response, $MLSModel);
                                break;
                            default:
                                $localization_entity->$l_method($value);
                                break;
                        }
                    }
                    $l_collection[] = $localization_entity;
                    if (!$translation_exists) {
                        $this->em->persists($localization_entity);
                    }
                }
                $entity->setLocalizations($l_collection);
                $this->em->persist($entity);
            }
        }
        $this->em->flush();

        $total_rows = count($to_update);
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $to_update,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.update.done',
        );
        return $this->response;
    }

    /**
     * @name 		deleteFaqCategory()
     * Deletes an existing item from database.
     *
     * @since		1.0.0
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->deleteFaqCategories()
     *
     * @param           mixed           $item           Entity, id or url key of item
     * @param           string          $by
     *
     * @return          mixed           $response
     */
    public function deleteFaqCategory($item, $by = 'entity') {
        return $this->deleteFaqCategories(array($item), $by);
    }

    /**
     * @name            deleteFaqCategories()
     * Deletes provided items from database.
     *
     * @since		1.0.0
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     *
     * @param           array           $collection     Collection of FaqCategory entities, ids, or codes or url keys
     * @param           string          $by             Accepts the following options: entity, id, code, url_key
     *
     * @return          array           $response
     */
    public function deleteFaqCategories($collection, $by = 'entity') {
        $this->resetResponse();
        $by_opts = array('entity', 'id', 'url_key');
        if (!in_array($by, $by_opts)) {
            return $this->createException('InvalidParameterValueException', 'err.invalid.parameter.collection', implode(',', $by_opts));
        }
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'err.invalid.parameter.collection', 'Array');
        }
        $entries = array();
        /** Loop through items and collect values. */
        $delete_count = 0;
        foreach ($collection as $item) {
            $value = '';
            if (is_object($item)) {
                if (!$item instanceof BundleEntity\FaqCategory) {
                    return $this->createException('InvalidParameterException', 'err.invalid.parameter.collection', 'BundleEntity\FaqCategory');
                }
                $this->em->remove($item);
                $delete_count++;
            } else if (is_numeric($item) || is_string($item)) {
                $value = $item;
            } else {
                /** If array values are not numeric nor object */
                return $this->createException('InvalidParameterException', 'err.invalid.parameter.collection', 'integer, string, or Module entity');
            }
            if (!empty($value) && $value != '') {
                $entries[] = $value;
            }
        }
        /**
         * Control if there is any entity ids in collection.
         */
        if (count($entries) < 1) {
            return $this->createException('InvalidParameterException', 'err.invalid.parameter.collection', 'Array');
        }
        $join_needed = false;
        /**
         * Prepare query string.
         */
        switch ($by) {
            case 'entity':
                /** Flush to delete all persisting objects */
                $this->em->flush();
                /**
                 * Prepare & Return Response
                 */
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => null,
                        'total_rows' => $delete_count,
                        'last_insert_id' => null,
                    ),
                    'error' => false,
                    'code' => 'scc.db.delete.done',
                );
                return $this->response;
            case 'id':
                $values = implode(',', $entries);
                break;
            /** Requires JOIN */
            case 'url_key':
                $join_needed = true;
                $values = implode('\',\'', $entries);
                $values = '\'' . $values . '\'';
                break;
        }
        if ($join_needed) {
            $q_str = 'DELETE ' . $this->entity['faq_category']['alias']
                    . ' FROM ' . $this->entity['faq_category_localization']['name'] . ' ' . $this->entity['faq_category_localization']['alias']
                    . ' JOIN ' . $this->entity['faq_category_localization']['name'] . ' ' . $this->entity['faq_category_localization']['alias']
                    . ' WHERE ' . $this->entity['faq_category_localization']['alias'] . '.' . $by . ' IN(:values)';
        } else {
            $q_str = 'DELETE ' . $this->entity['faq_category']['alias']
                    . ' FROM ' . $this->entity['faq_category']['name'] . ' ' . $this->entity['faq_category']['alias']
                    . ' WHERE ' . $this->entity['faq_category']['alias'] . '.' . $by . ' IN(:values)';
        }
        /**
         * Create query object.
         */
        $query = $this->em->createQuery($q_str);
        $query->setParameter('values', $entries);
        /**
         * Free memory.
         */
        unset($values);
        /**
         * 6. Run query
         */
        $query->getResult();
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $entries,
                'total_rows' => count($entries),
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.delete.done',
        );
        return $this->response;
    }

    /**
     * @name            listFaqCategories()
     * List items of a given collection.
     *
     * @since		1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     *
     * @use             $this->resetResponse()
     * @use             $this->createException()
     * @use             $this->prepare_where()
     * @use             $this->createQuery()
     * @use             $this->getResult()
     * 
     * @throws          InvalidSortOrderException
     * @throws          InvalidLimitException
     * 
     *
     * @param           mixed           $filter                Multi dimensional array
     * @param           array           $sortorder              Array
     *                                                              'column'    => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     * @param           string           $query_str             If a custom query string needs to be defined.
     *
     * @return          array           $response
     */
    public function listFaqCategories($filter = null, $sortorder = null, $limit = null, $query_str = null) {
        $this->resetResponse();
        if (!is_array($sortorder) && !is_null($sortorder)) {
            return $this->createException('InvalidSortOrderException', '', 'err.invalid.parameter.sortorder');
        }

        /**
         * Add filter check to below to set join_needed to true
         */
        $order_str = '';
        $where_str = '';
        $group_str = '';
        $filter_str = '';


        /**
         * Start creating the query
         *
         * Note that if no custom select query is provided we will use the below query as a start
         */
        $localizable = true;
        if (is_null($query_str)) {
            if ($localizable) {
                $query_str = 'SELECT ' . $this->entity['faq_category_localization']['alias']
                        . ' FROM ' . $this->entity['faq_category_localization']['name'] . ' ' . $this->entity['faq_category_localization']['alias']
                        . ' JOIN ' . $this->entity['faq_category_localization']['alias'] . '.COLUMN ' . $this->entity['faq_category']['alias'];
            } else {
                $query_str = 'SELECT ' . $this->entity['faq_category']['alias']
                        . ' FROM ' . $this->entity['faq_category']['name'] . ' ' . $this->entity['faq_category']['alias'];
            }
        }
        /*
         * Prepare ORDER BY section of query
         */
        if (!is_null($sortorder)) {
            foreach ($sortorder as $column => $direction) {
                switch ($column) {
                    case 'id':
                    case 'name':
                    case 'url_key':
                        break;
                }
                $order_str .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }

        /*
         * Prepare WHERE section of query
         */

        if (!is_null($filter)) {
            $filter_str = $this->prepare_where($filter);
            $where_str = ' WHERE ' . $filter_str;
        }



        $query_str .= $where_str . $group_str . $order_str;


        $query = $this->em->createQuery($query_str);

        /*
         * Prepare LIMIT section of query
         */

        if (!is_null($limit) && is_numeric($limit)) {
            /*
             * if limit is set
             */
            if (isset($limit['start']) && isset($limit['count'])) {
                $query = $this->addLimit($query, $limit);
            } else {
                $this->createException('InvalidLimitException', '', 'err.invalid.limit');
            }
        }
        //print_r($query->getSql()); die;
        /*
         * Prepare and Return Response
         */

        $files = $query->getResult();


        $total_rows = count($files);
        if ($total_rows < 1) {
            $this->response['error'] = true;
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }

        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $files,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );

        return $this->response;
    }

    /**
     * @name 		getFaqCategory()
     * Returns details of a gallery.
     *
     * @since		1.0.0
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     * @use             $this->listFaqCategories()
     *
     * @param           mixed           $item               id, url_key
     * @param           string          $by                 entity, id, url_key
     *
     * @return          mixed           $response
     */
    public function getFaqCategory($item, $by = 'id') {
        $this->resetResponse();
        $by_opts = array('id', 'url_key');
        if (!in_array($by, $by_opts)) {
            return $this->createException('InvalidParameterValueException', implode(',', $by_opts), 'err.invalid.parameter.by');
        }
        if (!is_object($item) && !is_numeric($item) && !is_string($item)) {
            return $this->createException('InvalidParameterException', 'FaqCategory', 'err.invalid.parameter');
        }
        if (is_object($item)) {
            if (!$item instanceof BundleEntity\FaqCategory) {
                return $this->createException('InvalidParameterException', 'FaqCategory', 'err.invalid.parameter');
            }
            /**
             * Prepare & Return Response
             */
            $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => $item,
                    'total_rows' => 1,
                    'last_insert_id' => null,
                ),
                'error' => false,
                'code' => 'scc.db.entry.exist',
            );
            return $this->response;
        }
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['faq_category_localization']['alias'] . '.' . $by, 'comparison' => '=', 'value' => $item),
                )
            )
        );

        $response = $this->listFaqCategories($filter, null, array('start' => 0, 'count' => 1));
        if ($response['error']) {
            return $response;
        }
        $collection = $response['result']['set'];
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $collection[0],
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 		doesFaqCategoryExist()
     * Checks if entry exists in database.
     *
     * @since		1.0.0
     * @version         1.0.0
     * @author          Said İmamoğlu
     *
     * @use             $this->getFaqCategory()
     *
     * @param           mixed           $item           id, url_key
     * @param           string          $by             id, url_key
     *
     * @param           bool            $bypass         If set to true does not return response but only the result.
     *
     * @return          mixed           $response
     */
    public function doesFaqCategoryExist($item, $by = 'id', $bypass = false) {
        $this->resetResponse();
        $exist = false;

        $response = $this->getFaqCategory($item, $by);

        if (!$response['error'] && $response['result']['total_rows'] > 0) {
            $exist = $response['result']['set'];
            $error = false;
        } else {
            $exist = false;
            $error = true;
        }

        if ($bypass) {
            return $exist;
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $exist,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => $error,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 		insertFaqCategory()
     * Inserts one or more item into database.
     *
     * @since		1.0.1
     * @version         1.0.3
     * @author          Said Imamoglu
     *
     * @use             $this->insertFiles()
     *
     * @param           array           $item        Collection of entities or post data.
     *
     * @return          array           $response
     */
    public function insertFaqCategory($item, $by = 'post') {
        $this->resetResponse();
        return $this->insertFaqCategories($item);
    }

    /**
     * @name            insertFaqCategories()
     * Inserts one or more items into database.
     *
     * @since           1.0.1
     * @version         1.0.3
     * @author          Said Imamoglu
     *
     * @use             $this->createException()
     *
     * @throws          InvalidParameterException
     * @throws          InvalidMethodException
     *
     * @param           array           $collection        Collection of entities or post data.
     * @param           string          $by                entity, post
     *
     * @return          array           $response
     */
    public function insertFaqCategories($collection, $by = 'post') {
        /* Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'array() or Integer', 'err.invalid.parameter.collection');
        }

        if (!in_array($by, $this->by_opts)) {
            return $this->createException('InvalidParameterException', implode(',', $this->by_opts), 'err.invalid.parameter.by.collection');
        }

        if ($by == 'entity') {
            $sub_response = $this->insert_entities($collection, 'BiberLtd\\Core\\Bundles\\FAQBundle\\Entity\\FaqCategory');
            /**
             * If there are items that cannot be deleted in the collection then $sub_Response['process']
             * will be equal to continue and we need to continue process; otherwise we can return response.
             */
            if ($sub_response['process'] == 'stop') {
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => $sub_response['entries']['valid'],
                        'total_rows' => $sub_response['item_count'],
                        'last_insert_id' => null,
                    ),
                    'error' => false,
                    'code' => 'scc.db.insert.done.',
                );

                return $this->response;
            } else {
                $collection = $sub_response['entries']['invalid'];
            }
        } elseif ($by == 'post') {
            $l_collection = array();
            $to_insert = 0;
            foreach ($collection as $item) {
                $localizations = array();
                if (isset($item['localizations'])) {
                    $localizations = $item['localizations'];
                    unset($item['localizations']);
                }
                $site = '';
                if (isset($item['site'])) {
                    $site = $item['site'];
                    unset($item['site']);
                }
                $entity = new BundleEntity\FaqCategory();
                foreach ($item as $column => $value) {
                    $method = 'set_' . $column;
                    if (method_exists($entity, $method)) {
                        $entity->$method($value);
                    }
                }
                /** HANDLE FOREIGN DATA :: LOCALIZATIONS */
                if (count($localizations) > 0) {
                    $l_collection[] = $localizations;
                }

                $this->insert_entities(array($entity), 'BiberLtd\\Core\\Bundles\\FAQBundle\\Entity\\FaqCategory');

                $entity_localizations = array();
                foreach ($l_collection as $localization) {
                    if ($localization instanceof BundleEntity\FaqCategoryLocalization) {
                        $entity_localizations[] = $localization;
                    } else {
                        $localization_entity = new BundleEntity\FaqCategoryLocalization;
                        $localization_entity->setFaqCategory($entity);
                        foreach ($localization as $key => $value) {
                            $l_method = 'set_' . $key;
                            switch ($key) {
                                case 'language';
                                    $MLSModel = new MLSService\MultiLanguageSupportModel($this->kernel, $this->db_connection, $this->orm);
                                    $response = $MLSModel->getLanguage($value, 'id');
                                    if ($response['error']) {
                                        new CoreExceptions\InvalidLanguageException($this->kernel, $value);
                                        break;
                                    }
                                    $language = $response['result']['set'];
                                    $localization_entity->setLanguage($language);
                                    unset($response, $MLSModel);
                                    break;
                                default:
                                    if (method_exists($localization_entity, $l_method)) {
                                        $localization_entity->$l_method($value);
                                    } else {
                                        new CoreExceptions\InvalidMethodException($this->kernel, $method);
                                    }
                                    break;
                            }
                            $entity_localizations[] = $localization_entity;
                        }
                    }
                }
                $this->insert_entities($l_collection, 'BiberLtd\Core\\Bundles\\FAQBundle\\Entity\\FaqCategoryLocalization');
                /**
                 * ????? DO we really need this?
                 *
                 * Test! Also check if you can make use of insert_localizations functions but of course this will require
                 * dependency on MLSModel.
                 */
                $entity->setLocalizations($entity_localizations);
                $this->em->persist($entity);
                $to_insert++;
                /** Free some memory */
                unset($entity_localizations);
            }
            $this->em->flush();
            $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => $collection,
                    'total_rows' => count($collection),
                    'last_insert_id' => $entity->getId(),
                ),
                'error' => false,
                'code' => 'scc.db.insert.done',
            );

            return $this->response;
        }
    }

    /*
     * @name            updateFaqCategory()
     * Updates single item. The item must be either a post data (array) or an entity
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->resetResponse()
     * @use             $this->updateFaqCategories()
     * 
     * @param           mixed   $item     Entity or Entity id of a folder
     * 
     * @return          array   $response
     * 
     */

    public function updateFaqCategory($item) {
        $this->resetResponse();
        return $this->updateFaqCategories(array($item));
    }

    /*
     * @name            updateFaqCategories()
     * Updates one or more item details in database.
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->update_entities()
     * @use             $this->createException()
     * @use             $this->listFaqCategories()
     * 
     * 
     * @throws          InvalidParameterException
     * 
     * @param           array   $collection     Collection of item's entities or array of entity details.
     * @param           array   $by             entity or post
     * 
     * @return          array   $response
     * 
     */

    public function updateFaqCategories($collection, $by = 'post') {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $by_opts = array('entity', 'post');
        if (!in_array($by, $by_opts)) {
            return $this->createException('InvalidParameterException', implode(',', $by_opts), 'err.invalid.parameter.by');
        }
        if ($by == 'entity') {
            $sub_response = $this->update_entities($collection, 'BiberLtd\\Core\\Bundles\\FAQBundle\\Entity\\FaqCategory');
            /**
             * If there are items that cannot be deleted in the collection then $sub_Response['process']
             * will be equal to continue and we need to continue process; otherwise we can return response.
             */
            if ($sub_response['process'] == 'stop') {
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => $sub_response['entries']['valid'],
                        'total_rows' => $sub_response['item_count'],
                        'last_insert_id' => null,
                    ),
                    'error' => false,
                    'code' => 'scc.db.delete.done',
                );
                return $this->response;
            } else {
                $collection = $sub_response['entries']['invalid'];
            }
        }
        /**
         * If by post
         */
        $to_update = array();
        $count = 0;
        $collection_by_id = array();
        foreach ($collection as $item) {
            if (!isset($item['id'])) {
                unset($collection[$count]);
            }
            $to_update[] = $item['id'];
            $collection_by_id[$item['id']] = $item;
            $count++;
        }
        unset($collection);
        $filter = array(
            array(
                'glue' => 'and',
                'condition' => array(
                    array(
                        'glue' => 'and',
                        'condition' => array('column' => $this->entity['faq_category']['alias'] . '.id', 'comparison' => 'in', 'value' => $to_update),
                    )
                )
            )
        );
        $response = $this->listFaqCategories($filter);
        if ($response['error']) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $entities = $response['result']['set'];
        foreach ($entities as $entity) {
            $data = $collection_by_id[$entity->getId()];
            /** Prepare foreign key data for process */
            $localizations = array();
            if (isset($data['localizations'])) {
                $localizations = $data['localizations'];
            }
            unset($data['localizations']);
            $site = '';
            if (isset($data['site'])) {
                $site = $data['site'];
            }
            unset($data['site']);

            foreach ($data as $column => $value) {
                $method_set = 'set_' . $column;
                $method_get = 'get_' . $column;
                /**
                 * Set the value only if there is a corresponding value in collection and if that value is different
                 * from the one set in database
                 */
                if (isset($collection_by_id[$entity->getId()][$column]) && $collection_by_id[$entity->getId()][$column] != $entity->$method_get()) {
                    $entity->$method_set($value);
                }
                /** HANDLE FOREIGN DATA :: LOCALIZATIONS */
                $l_collection = array();
                foreach ($localizations as $lang => $localization) {
                    $MLSModel = new MLSService\MultiLanguageSupportModel($this->kernel, $this->db_connection, $this->orm);
                    $response = $MLSModel->getLanguage($lang, 'iso_code');
                    if ($response['error']) {
                        new CoreExceptions\InvalidLanguageException($this->kernel, $value);
                        break;
                    }
                    $language = $response['result']['set'];
                    $translation_exists = true;
                    $response = $this->getFaqCategoryLocalization($entity, $language);
                    if ($response['error']) {
                        $localization_entity = new BundleEntity\FaqCategoryLocalization();
                        $translation_exists = false;
                    } else {
                        $localization_entity = $response['result']['set'];
                    }
                    foreach ($localization as $key => $value) {
                        $l_method = 'set_' . $key;
                        switch ($key) {
                            case 'product':
                                $localization_entity->setFaqCategory($entity);
                                break;
                            case 'language';
                                $language = $response['result']['set'];
                                $localization_entity->setLanguage($language);
                                unset($language, $response, $MLSModel);
                                break;
                            default:
                                $localization_entity->$l_method($value);
                                break;
                        }
                    }
                    $l_collection[] = $localization_entity;
                    if (!$translation_exists) {
                        $this->em->persists($localization_entity);
                    }
                }
                $entity->setLocalizations($l_collection);
                $this->em->persist($entity);
            }
        }
        $this->em->flush();

        $total_rows = count($to_update);
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $to_update,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.update.done',
        );
        return $this->response;
    }

}

/**
 * 
 * Change Log
 * **************************************
 * v1.0.0                      Said İmamoğlu
 * 25.12.2013
 * **************************************
 * A __construct()
 * A __destruct()
 * A deleteFaq()
 * A deleteFaqs()
 * A listFaqs()
 * A getFaq()
 * A doesFaqExist()
 * A insertFaq()
 * A insertFaqs()
 * A updateFaq()
 * A updateFaqs()
 * 
 * A deleteFaqCategory()
 * A deleteFaqCategories()
 * A listFaqCategories()
 * A getFaqCategory()
 * A doesFaqCategoryExist()
 * A insertFaqCategory()
 * A insertFaqCategories()
 * A updateFaqCategory()
 * A updateFaqCategories()
 * 
 * 
 * 
*/
    