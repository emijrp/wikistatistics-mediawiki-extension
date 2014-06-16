<?php

#http://www.mediawiki.org/wiki/Manual:Special_pages
#http://www.mediawiki.org/wiki/Manual:OutputPage.php
#http://people.iola.dk/olau/flot/API.txt

/**
 * Ideas: user-by-user analysis, page-by-page analysis, 
 * 
 * 
 * 
 */
class SpecialWikiStats extends SpecialPage {
    
    function __construct() {
        parent::__construct( 'WikiStatistics' );
    }
    
    /**
     * Make your magic happen!
     */    
    function execute( $par ) {
        global $wgLang, $wgOut;
        $wgOut->addModuleScripts( 'ext.WikiStatistics' );
        $wgOut->setPageTitle( wfMsg( 'wikistatistics-pagetitle' ) );
        $wgOut->addWikiMsg( 'wikistatistics-welcome' );
        
        $graphwidth = '800px';
        $graphheight = '300px';
        $limit = 25;
        $dbr = wfGetDB( DB_SLAVE );
        
        $totalusers = $this->getTotalUsers($dbr);
        $totalpages = $this->getTotalPages($dbr);
        $totalrevisions = $this->getTotalRevisions($dbr);
        $totalbytes = $this->getTotalBytes($dbr);
        $totalfiles = $this->getTotalFiles($dbr);
        $totalvisits = $this->getTotalVisits($dbr);
        
        $wgOut->addHTML( '<h2>'.wfMsg( 'wikistatistics-global-summary-header') .'</h2>');
        $wgOut->addWikiMsg( 'wikistatistics-basic-stats', $wgLang->formatNum($totalusers), $wgLang->formatNum(0), $wgLang->formatNum(0), $wgLang->formatNum($totalpages), $wgLang->formatNum($totalrevisions), $wgLang->formatNum($totalbytes), $wgLang->formatNum($totalfiles), $wgLang->formatNum($totalvisits) );
        
        $sql = "SELECT CONCAT(YEAR(rev_timestamp),'-',LPAD(MONTH(rev_timestamp), 2, 0),'-',LPAD(DAY(rev_timestamp), 2, 0),'T00:00:00Z') AS timestamp, count(rev_id) AS rev_count from revision WHERE 1 GROUP BY timestamp ORDER BY timestamp ASC";
        $res = $dbr->query( $sql );
        $d1 = '';
        foreach ( $res as $row ) {
            $d1 .= '["'.$this->mwtime2unixtime($row->timestamp).'", '.$row->rev_count.'], ';
        }
        $dbr->freeResult( $res );
        
        $wgOut->addHTML( '<h2>'.wfMsg( 'wikistatistics-global-activity-header') .'</h2>');
        $wgOut->addHTML( '<h3>'.wfMsg( 'wikistatistics-global-activity-all-header') .'</h3>');
        
        $wgOut->addHTML( '<div id="placeholder" style="width:'.$graphwidth.';height:'.$graphheight.';"></div>
<script type="text/javascript">
$(function () {
    var d1 = ['.$d1.'];
    
    var placeholder = $("#placeholder");
    var data = [ d1, ];
    var options = { xaxis: { mode: "time", monthNames: ["'.wfMsg('wikistatistics-month-jan').'", "'.wfMsg('wikistatistics-month-feb').'", "'.wfMsg('wikistatistics-month-mar').'", "'.wfMsg('wikistatistics-month-apr').'", "'.wfMsg('wikistatistics-month-may').'", "'.wfMsg('wikistatistics-month-jun').'", "'.wfMsg('wikistatistics-month-jul').'", "'.wfMsg('wikistatistics-month-aug').'", "'.wfMsg('wikistatistics-month-sep').'", "'.wfMsg('wikistatistics-month-oct').'", "'.wfMsg('wikistatistics-month-nov').'", "'.wfMsg('wikistatistics-month-dec').'" ], }, lines: { show: true }, points: { show: true }, legend: { noColumns: 1 }, grid: { hoverable: true }, };
    $.plot(placeholder, data, options);
});
</script>' );
        
        
        $sql = "SELECT LPAD(HOUR(rev_timestamp), 2, 0) AS timestamp, count(rev_id) AS rev_count from revision WHERE 1 GROUP BY timestamp ORDER BY timestamp ASC";
        $res = $dbr->query( $sql );
        $d2 = '';
        foreach ( $res as $row ) {
            $d2 .= '["'.$row->timestamp.'", '.$row->rev_count.'], ';
        }
        $dbr->freeResult( $res );
        
        $wgOut->addHTML( '<h3>'.wfMsg( 'wikistatistics-global-activity-hourly-header') .'</h3>');
        $wgOut->addHTML( '<div id="placeholder2" style="width:'.$graphwidth.';height:'.$graphheight.';"></div>
<script type="text/javascript">
$(function () {
    var d2 = ['.$d2.'];
    
    var placeholder2 = $("#placeholder2");
    var data2 = [ d2, ];
    var options2 = { xaxis: { mode: null, tickSize: 1, tickDecimals: 0, min: 1, max: 23}, bars: { show: true, barWidth: 0.6 }, points: { show: false }, legend: { noColumns: 1 }, grid: { hoverable: true }, };
    $.plot(placeholder2, data2, options2);
});
</script>' );
        
        
        $wgOut->addHTML( '<h3>'.wfMsg( 'wikistatistics-global-activity-daily-header') .'</h3>');
        $wgOut->addHTML( '<h3>'.wfMsg( 'wikistatistics-global-activity-weekly-header') .'</h3>');
        $wgOut->addHTML( '<h3>'.wfMsg( 'wikistatistics-global-activity-monthly-header') .'</h3>');

        $sql = "SELECT rev_user, rev_user_text, count(rev_id) as rev_count FROM revision WHERE 1 GROUP BY rev_user ORDER BY rev_count DESC LIMIT " . $limit;
        $res = $dbr->query( $sql );
        $wgOut->addHTML( '<h2>'.wfMsg( 'wikistatistics-users-ranking-header') .'</h2>' );
        $wgOut->addHTML( '<table class="wikitable sortable" style="text-align: center;">' );
        $wgOut->addHTML( '<tr><th>'.wfMsg('wikistatistics-username-th').'</th><th>'.wfMsg('wikistatistics-edits-th').'</th><th>%</th></tr>' );
        foreach ( $res as $row ) {
            $wgOut->addHTML( '<tr><td>'.Linker::userLink( $row->rev_user, $row->rev_user_text).'</td><td>'.$row->rev_count.'</td><td>'.round($row->rev_count/($totalrevisions/100), 2).'%</td></tr>' );
        }
        $wgOut->addHTML( '<tr><td><b>Total</b></td><td>'.$totalrevisions.'</td><td>100%</td></tr>' );
        $dbr->freeResult( $res );
        $wgOut->addHTML( '</table>' );

    }
    function mwtime2unixtime($t)
    {
        //2012-01-01T00:00:00Z
        return mktime((int)substr($t, 11, 2), (int)substr($t, 14, 2), (int)substr($t, 17, 2), (int)substr($t, 5, 2), (int)substr($t, 8, 2), (int)substr($t, 0, 4))*1000;
    }
    function getTotalUsers( $dbr )
    {
        $sql = "SELECT count(DISTINCT rev_user) AS count FROM revision WHERE rev_user!=0";
        $res = $dbr->query( $sql );
        foreach ( $res as $row ) {
            return $row->count;
        }
    }
    function getTotalPages( $dbr )
    {
        $sql = "SELECT count(DISTINCT page_id) AS count FROM page WHERE 1";
        $res = $dbr->query( $sql );
        foreach ( $res as $row ) {
            return $row->count;
        }
    }
    function getTotalRevisions( $dbr )
    {
        $sql = "SELECT count(rev_id) AS count FROM revision WHERE 1";
        $res = $dbr->query( $sql );
        foreach ( $res as $row ) {
            return $row->count;
        }
    }
    
    function getTotalBytes( $dbr )
    {
        $sql = "SELECT SUM(page_len) AS count FROM page WHERE 1";
        $res = $dbr->query( $sql );
        foreach ( $res as $row ) {
            return $row->count;
        }
    }
    
    function getTotalFiles( $dbr )
    {
        $sql = "SELECT count(img_name) AS count FROM image WHERE 1";
        $res = $dbr->query( $sql );
        foreach ( $res as $row ) {
            return $row->count;
        }
    }
    
    function getTotalVisits( $dbr )
    {
        $sql = "SELECT SUM(page_counter) AS count FROM page WHERE 1";
        $res = $dbr->query( $sql );
        foreach ( $res as $row ) {
            return $row->count;
        }
    }
    
}
