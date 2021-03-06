<?php

/**
 * @property SiteTree $owner
 */
class GalleryExtension extends SiteTreeExtension {

    private static $has_many = [
        'PrimaryGalleryImages'   => 'PrimaryGalleryImage',
        'SecondaryGalleryImages' => 'SecondaryGalleryImage',
    ];

    public function updateCMSFields(FieldList $fields) {

        foreach (['primary', 'secondary'] as $lower) {
            $upper  = ucfirst($lower);
            $config = $this->owner->config()->get("{$lower}_gallery");
            if (is_null($config) || (isset($config['enabled']) && $config['enabled'] === false)) continue;

            $config['title']  = isset($config['title']) ? $config['title'] : "{$upper} Gallery";
            $config['folder'] = isset($config['folder']) ? $config['folder'] : "{$upper}-Gallery-Images";

            $GridFieldConfig = new GridFieldConfig_RecordEditor();
            $GridFieldConfig->removeComponentsByType('GridFieldAddNewButton');
            $GridFieldConfig->addComponent($bulkUploadConfig = new GridFieldBulkUpload());
            $GridFieldConfig->addComponent(new GridFieldSortableRows('SortOrder'));
            $GridFieldConfig->addComponent(new GridFieldGalleryTheme('Image'));
            $bulkUploadConfig->setUfSetup('setFolderName', "Images/{$config['folder']}");
            $GridField = new GridField("{$upper}GalleryGridField", $config['title'], $this->owner->{"{$upper}GalleryImages"}(), $GridFieldConfig);

            /** @var TabSet $rootTab */
            //We need to repush Metadata to ensure it is the last tab
            $rootTab = $fields->fieldByName('Root');
            $rootTab->push($tab = Tab::create("{$upper}Gallery"));
            if ($rootTab->fieldByName('Metadata')) {
                $metaChildren = $rootTab->fieldByName('Metadata')->getChildren();
                $rootTab->removeByName('Metadata');
                $rootTab->push(Tab::create('Metadata')->setChildren($metaChildren));
            }

            $tab->setTitle($config['title']);
            $fields->addFieldToTab("Root.{$upper}Gallery", $GridField);

        }
    }


}
