<?php
class MoodleQuickForm_simpleeditor extends MoodleQuickForm_editor {
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        $this->_options['atto:toolbar'] = '';
        parent::__construct($elementName, $elementLabel, $attributes, $options);
    }
} 