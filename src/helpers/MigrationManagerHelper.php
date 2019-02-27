<?php

namespace firstborn\migrationmanager\helpers;

use Craft;
use craft\models\FolderCriteria;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\Tag;
use craft\elements\User;
use craft\records\GlobalSet;
use craft\records\TagGroup;

/**
 * Class MigrationManagerHelper
 */
class MigrationManagerHelper
{
    /**
     * @param string            $handle
     * @param string|array|null $context
     *
     * @return bool|FieldModel
     */
    public static function getFieldByHandleContext($handle, $context)
    {
        $fields = Craft::$app->fields->getAllFields(null, $context);
        foreach ($fields as $field) {
            if ($field->handle == $handle) {
                return $field;
            }
        }

        return false;
    }

    /**
     * @param $handle
     * @param $fieldId
     *
     * @return bool|MatrixBlockTypeModel
     */
    public static function getMatrixBlockType($handle, $fieldId)
    {
        $blockTypes = Craft::$app->matrix->getBlockTypesByFieldId($fieldId);
        foreach ($blockTypes as $block) {
            if ($block->handle == $handle) {
                return $block;
            }
        }

        return false;
    }

    /**
     * @param $handle
     * @param $fieldId
     *
     * @return bool|NeoBlockTypeModel
     */
    public static function getNeoBlockType($handle, $fieldId)
    {
        $neo = Craft::$app->plugins->getPlugin('neo');
        $blockTypes = $neo->blockTypes->getByFieldId($fieldId);
        foreach ($blockTypes as $block) {
            if ($block->handle == $handle) {
                return $block;
            }
        }

        return false;
    }

    /**
     * @param array $element
     *
     * @return bool|BaseElementModel|null
     * @throws Exception
     */
    public static function getAssetByHandle($element)
    {
        $volume = Craft::$app->volumes->getVolumeByHandle($element['source']);
        if ($volume) {

            $folderCriteria = new FolderCriteria();
            $folderCriteria->name = $element['folder'];
            $folderCriteria->volumeId = $volume->id;

            $folder = Craft::$app->assets->findFolder($folderCriteria);
            if ($folder) {

                $query = Asset::find();
                $query->volumeId($volume->id);
                $query->folderId($folder->id);
                $query->filename($element['filename']);
                $asset = $query->one();

                if ($asset) {
                    return $asset;
                }
            }
        }

        return false;
    }

    /**
     * @param array $element
     *
     * @return bool|BaseElementModel|null
     * @throws Exception
     */
    public static function getCategoryByHandle($element)
    {
        $categoryGroup = Craft::$app->categories->getGroupByHandle($element['category']);
        if ($categoryGroup) {

            $query = Category::find();
            $query->groupId($categoryGroup->id);
            $query->slug($element['slug']);
            $category = $query->one();

            if ($category) {
                return $category;
            }
        }

        return false;
    }

    /**
     * @param array $element
     *
     * @return bool|BaseElementModel|null
     * @throws Exception
     */
    public static function getEntryByHandle($element)
    {
        $section = Craft::$app->sections->getSectionByHandle($element['section']);
        if ($section) {

            $query = Entry::find();
            $query->sectionId($section->id);
            $query->slug($element['slug']);
            $entry = $query->one();

            if ($entry) {
                return $entry;
            }
        }

        return false;
    }

    /**
     * @param array $element
     *
     * @return bool|UserModel|null
     */
    public static function getUserByHandle($element)
    {
        $user = Craft::$app->users->getUserByUsernameOrEmail($element['username']);
        if ($user) {
            return $user;
        }

        return false;
    }

    /**
     * @param array $element
     *
     * @return BaseElementModel|null
     * @throws Exception
     */
    public static function getTagByHandle($element)
    {
        $group = Craft::$app->tags->getTagGroupByHandle($element['group']);
        if ($group) {

            $query = Tag::find();
            $query->groupId($group->id);
            $query->slug($element['slug']);
            $tag = $query->one();

            if ($tag) {
                return $tag;
            }
        }
    }

    /**
     * @param array $permissions
     *
     * @return array
     */
    public static function getPermissionIds($permissions)
    {
        foreach ($permissions as &$permission) {
            //determine if permission references element, get id if it does
            if (preg_match('/(:)/', $permission)) {
                $permissionParts = explode(":", $permission);
                $element = null;

                if (preg_match('/entries|entrydrafts/', $permissionParts[0])) {
                    $element = Craft::$app->sections->getSectionByHandle($permissionParts[1]);
                } elseif (preg_match('/volume/', $permissionParts[0])) {
                    $element = Craft::$app->volumes->getVolumeByHandle($permissionParts[1]);
                } elseif (preg_match('/categories/', $permissionParts[0])) {
                    $element = Craft::$app->categories->getGroupByHandle($permissionParts[1]);
                } elseif (preg_match('/globalset/', $permissionParts[0])) {
                    $element = Craft::$app->globals->getSetByHandle($permissionParts[1]);
                }

                if ($element != null) {
                    $permission = $permissionParts[0].':'. (MigrationManagerHelper::isVersion('3.1')  ? $element->uid : $element->id);
                }
            }
        }

        return $permissions;
    }

    /**
     * @param array $permissions
     *
     * @return array
     */
    public static function getPermissionHandles($permissions)
    {
        foreach ($permissions as &$permission) {
            //determine if permission references element, get handle if it does
            if (preg_match('/(:\w)/', $permission)) {
                $permissionParts = explode(":", $permission);
                $element = null;
                $hasUids = MigrationManagerHelper::isVersion('3.1');
                
                if (preg_match('/entries|entrydrafts/', $permissionParts[0])) {
                    $element = $hasUids ? Craft::$app->sections->getSectionByUid($permissionParts[1]) : Craft::$app->sections->getSectionById($permissionParts[1]);
                } elseif (preg_match('/volume/', $permissionParts[0])) {
                    $element = $hasUids ? Craft::$app->volumes->getVolumeByUid($permissionParts[1]) : Craft::$app->volumes->getVolumeById($permissionParts[1]);
                } elseif (preg_match('/categories/', $permissionParts[0])) {
                    $element = $hasUids ? Craft::$app->categories->getGroupByUid($permissionParts[1]) : Craft::$app->categories->getGroupById($permissionParts[1]);
                } elseif (preg_match('/globalset/', $permissionParts[0])) {
                    $element = $hasUids ? MigrationManagerHelper::getGlobalSetByUid($permissionParts[1]) : Craft::$app->globals->getSetByid($permissionParts[1]);
                }           

                if ($element != null) {
                    $permission = $permissionParts[0].':'.$element->handle;
                }
            }
        }

        return $permissions;
    }

    /**
     * check to see if current version is greater than or equal to a version
     */
    public static function isVersion($version){
        $currentVersion = Craft::$app->getVersion();
        $version = explode('.', $version);
        $currentVersion = explode('.', $currentVersion);
        $isVersion = true;
        foreach($version as $key => $value){
            if ((int)$currentVersion[$key] < $version[$key]){ 
                $isVersion = false;
            }
        }
        return $isVersion;
    }

    /**
     * Get a tag record by uid
     */

    public static function getTagGroupByUid(string $uid): TagGroup
    {
        $query = TagGroup::find();
        $query->andWhere(['uid' => $uid]);
        return $query->one() ?? new TagGroup();
    }

     /**
     * Gets a global set's record by uid.
     *
     * @param string $uid
     * @return GlobalSetRecord
     */
    public static function getGlobalSetByUid(string $uid): GlobalSet
    {
        return GlobalSet::findOne(['uid' => $uid]) ?? new GlobalSet();
    }

    /**
     * Create a web friendly URL slug from a string.
     * 
     * Although supported, transliteration is discouraged because
     *     1) most web browsers support UTF-8 characters in URLs
     *     2) transliteration causes a loss of information
     *
     * @author Sean Murphy <sean@iamseanmurphy.com>
     * @copyright Copyright 2012 Sean Murphy. All rights reserved.
     * @license http://creativecommons.org/publicdomain/zero/1.0/
     *
     * @param string $str
     * @param array $options
     * @return string
     */
    public static function slugify($str, $options = array()) {
        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
        
        $defaults = array(
            'delimiter' => '-',
            'limit' => null,
            'lowercase' => true,
            'replacements' => array(),
            'transliterate' => true,
        );
        
        // Merge options
        $options = array_merge($defaults, $options);

     
        $char_map = array(
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
            'ß' => 'ss', 
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 
            'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 
            'ÿ' => 'y',
            // Latin symbols
            '©' => '(c)',
            // Greek
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
            // Turkish
            'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
            'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 
            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',
            // Ukrainian
            'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
            'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
            // Czech
            'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 
            'Ž' => 'Z', 
            'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
            'ž' => 'z', 
            // Polish
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
            'Ż' => 'Z', 
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',
            // Latvian
            'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 
            'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
            'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
            'š' => 's', 'ū' => 'u', 'ž' => 'z',
            //Scandinavian
            'Å' => 'A', 'å' => 'a', 'Ä' => 'A', 'ä' => 'a', 'Æ' => 'AE', 'æ' => 'ae', 'Ä' => 'A', 'ä' => 'a', 
            'Ö' => 'O', 'ö' => 'o', 'O' => 'A', 'ø' => 'o',

           


        );
       
        // Make custom replacements
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
        
        // Transliterate characters to ASCII
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }
        
        // Replace non-alphanumeric characters with our delimiter
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
        
        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
        
        // Truncate slug to max. characters
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
        
        // Remove delimiter from ends
        $str = trim($str, $options['delimiter']);
        
        $str = $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;

        return $str;
    }

}
