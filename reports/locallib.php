<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * 
 * @param unknown $divid
 * @param array $labels
 * @param array $data
 * @param unknown $title
 * @param string $xtitle
 * @param string $ytitle
 * @return multitype:string
 */
function emarking_get_google_chart($divid, array $labels, array $data, $title, $xtitle = NULL, $ytitle = NULL)
{
    // DIV for displaying
    $html = '<div id="'.$divid.'" style="width: 100%; height: 500px;"></div>';
    
    // Headers
    $labelsjs = "['".implode("', '", $labels)."']";
    
    // Data JS
    $datajs = "";
    for($i=0; $i<count($data); $i++) {
        $datajs .= "[";
        for($j=0;$j<count($data[$i]); $j++) {
            $datacell = $data[$i][$j];
            if($j == 0) {
                $datacell = "'".$datacell."'";
            }
            if($j<count($data[$i])-1) {
                $datacell = $datacell . ",";
            }
            $datajs .= $datacell;
        }
        $datajs .= "],";
    }
    
    // The required JS to display the chart
    $js = "
        google.setOnLoadCallback(drawChart$divid);

        // Chart function for $divid
        function drawChart$divid() {
          
        var data = google.visualization.arrayToDataTable([
            $labelsjs,
            $datajs
            ]);

        var options = {
                        animation: {duration: 500},
                        title: '$title',
                        hAxis: {title: '$xtitle', titleTextStyle: {color: 'black'}, format:'#'},
                        vAxis: {title: '$ytitle', titleTextStyle: {color: 'black'}, format:'#'},
                        legend: 'top',
                        vAxes: {
                                0: {
                                    gridlines: {color: '#ddd'},
                                    format:'#'
                                   },
                                1: {
                                    gridlines: {color: '#ddd'},
                                    format:'#'
                                   },
                                },
                       series: {
                                0:{targetAxisIndex:0},
                                1:{targetAxisIndex:1},
                                2:{targetAxisIndex:1},
}
                      };

        var chart = new google.visualization.LineChart(document.getElementById('$divid'));
        chart.draw(data, options);
       }";
    
    return array($html, $js);
}

/**
 * Navigation tabs for reports
 *
 * @param unknown $category
 *            The category object
 * @return multitype:tabobject array of tabobjects
 */
function emarking_reports_tabs($category)
{
    $tabs = array();

    // Statistics
    $statstab = new tabobject("statistics", new moodle_url("/mod/emarking/reports/print.php", array(
        "category" => $category->id
    )), get_string("statistics", 'mod_emarking'));

    // Print statistics
    $statstab->subtree[] = new tabobject("printstatistics", new moodle_url("/mod/emarking/reports/print.php", array(
        "category" => $category->id
    )), get_string("statistics", 'mod_emarking'));

    // Print statistics
    $statstab->subtree[] = new tabobject("printdetails", new moodle_url("/mod/emarking/reports/printdetails.php", array(
        "category" => $category->id
    )), get_string("printdetails", 'mod_emarking'));

    $tabs[] = $statstab;
    return $tabs;
}

