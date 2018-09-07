<?php
namespace WebbuildersGroup\DeploymentNotes\View;

use WebbuildersGroup\DeploymentNotes\Model\DeploymentNote;
use SilverStripe\Control\Controller;
use SilverStripe\CMS\Controllers\SilverStripeNavigatorItem;


if (!class_exists(SilverStripeNavigatorItem::class)) {
    return;
}

class DeploymentNavigatorItem_LiveLink extends SilverStripeNavigatorItem {
    /**
     * Checks to see if the record is an instance of a deployment note or not
     * @return bool
     */
    public function canView($member=null) {
        return ($this->record instanceof DeploymentNote);
    }
    
    /**
     * Returns the title for this link
     * @return string
     */
    public function getTitle() {
        return _t('WebbuildersGroup\\DeploymentNotes\\Control\\Admin\\DeploymentScheduleAdmin.PREVIEW', '_Preview');
    }
    
    /**
     * Gets the html for rendering this link
     * @return string
     */
    public function getHTML() {
        $this->recordLink = Controller::join_links($this->record->AbsoluteLink());
        return '<a '.($this->isActive() ? 'class="current" ':'').' href="'.$this->recordLink.'">'._t('WebbuildersGroup\\DeploymentNotes\\Control\\Admin\\DeploymentScheduleAdmin.PREVIEW', '_Preview').'</a>';
    }
    
    /**
     * Gets the relative link to preview the record
     * @return string
     */
    public function getLink() {
        return Controller::join_links($this->record->PreviewLink());
    }
    
    /**
     * Determins whether or not this link is active or not
     * @return bool
     */
    public function isActive() {
        return true;
    }
}
?>