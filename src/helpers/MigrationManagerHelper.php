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
        $blockTypes = Craft::$app->neo->getBlockTypesByFieldId($fieldId);
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

}