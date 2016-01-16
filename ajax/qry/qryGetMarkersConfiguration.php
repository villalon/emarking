<?php

$results = array();

// Get rubric instance
list($gradingmanager, $gradingmethod, $definition) = emarking_validate_rubric($context);

$results['rubricname'] = $definition->name;

$results['criteria'] = array();
foreach($definition->rubric_criteria as $criterion) {
	$results['criteria'][] = array('id' => $criterion['id'],'description' => $criterion['description']);
}

// Generate markers list
$results['markers'] = array();
$indices = array();

// Get all users with permission to grade in emarking
$markers=get_enrolled_users($context, 'mod/emarking:grade');

// Add all users to markers list, we set criterion to 0
$i=0;
foreach($markers as $marker) {
	$results['markers'][] = array('id'=>$marker->id, 'fullname'=>$marker->firstname . ' ' . $marker->lastname, 'criteria'=>array());
	$indices[$marker->id] = $i;
	$i++;
}

// We get previous configuration of criteria for markers and set accordingly
$markerscriteria = $DB->get_records('emarking_marker_criterion', array('emarking'=>$emarking->id));
foreach($markerscriteria as $markercriterion) {
	$results['markers'][$indices[$markercriterion->marker]]['criteria'][] = array('id'=>$markercriterion->criterion);
} 


// Generate pages list
$results['pages'] = array();
$indices = array();

// We create a list of pages according to the total pages configured for emarking
// All pages are set to criterion 0
for($i=1; $i<=$emarking->totalpages; $i++) {
	$results['pages'][] = array('page'=>$i, 'criteria'=>array());
}

// We load previous configuration of page criterion assignments
$pagescriteria = $DB->get_records('emarking_page_criterion', array('emarking'=>$emarking->id));
foreach($pagescriteria as $pagecriterion) {
	$results['pages'][($pagecriterion->page-1	)]['criteria'][] = array('id'=>$pagecriterion->criterion);
}
