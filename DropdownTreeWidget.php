<?php

namespace delikatesnsk\treedropdown;

class DropdownTreeWidget extends \yii\base\Widget
{
    public $form = null; // ActiveForm
    public $model = null; // model
    public $attribute = null; // model attribute
    public $multiSelect = 'auto'; // true, false or 'auto'
    public $searchPanel = [ 
        'visible' => false, 
        'label' => '', // text before search input 
        'placeholder' => '',  // search input placeholder text
        'searchCaseSensivity' => false 
    ];
    public $rootNode = [
        'visible' => true,  
        'label' => 'Root' 
    ];

    public $expand = false; // expand dropdown tree after show
    public $ajax = null;
    public $label = false; // label of dropdown
    public $items = null; // array of tree nodes with subnodes

    private $html = '';
    private $treeObject = null;

    private function buildTreeObject($items, &$parentItem) {
        foreach ($items as $item) {
            if (is_array($item) && isset($item['id']) && isset($item['label'])) {
                $node = new \stdClass();
                $node->id = $item['id'];
                $node->label = $item['label'];
                $node->items = []; // Initialize items for children
                if (isset($item['items']) && is_array($item['items'])) {
                    $this->buildTreeObject($item['items'], $node); // Recursion for children
                }
                // Attach the node to its parent's items
                if (!isset($parentItem->items)) {
                    $parentItem->items = [];
                }
                $parentItem->items[] = $node;
            }
        }
    }

    public function buildTreeView($items) {
        if (is_array($items) && count($items) > 0) {
            $this->html .= "<ul>\n"; // Start a new unordered list
            foreach ($items as $item) {
                if (is_object($item) && isset($item->id) && isset($item->label)) {
                    $this->html .= "<li" . (isset($item->items) && count($item->items) > 0 ? " class=\"parent\"" : "") . ">\n";
                    $this->html .= "    <div class=\"node\">\n";
                    $this->html .= "        <span data-id='" . $item->id . "'>" . $item->label . "</span>\n";
                    $this->html .= "    </div>\n";
                    // Check for children and call recursively
                    if (isset($item->items) && count($item->items) > 0) {
                        $this->buildTreeView($item->items);
                    }
                    $this->html .= "</li>\n"; // Close the list item
                }
            }
            $this->html .= "</ul>\n"; // Close the unordered list
        }
    }

    public function init()
    {
        parent::init();
        // Initialize multiSelect
        if (!is_bool($this->multiSelect)) {
            $this->multiSelect = mb_convert_case($this->multiSelect, MB_CASE_LOWER) === 'auto' && $this->form instanceof \yii\widgets\ActiveForm;
        }

        // Initialize tree object with root
        $this->treeObject = new \stdClass();
        $this->treeObject->id = -1;
        $this->treeObject->label = $this->rootNode['label'];
        $this->treeObject->items = [];
        $this->buildTreeObject($this->items, $this->treeObject);
        $this->buildTreeView($this->treeObject->items);
    }

    public function run()
    {
        return $this->render('view', ['htmlData' => $this->html]);
    }
}
