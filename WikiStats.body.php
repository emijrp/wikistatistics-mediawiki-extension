<?php

#http://www.mediawiki.org/wiki/Manual:Special_pages
#http://www.mediawiki.org/wiki/Manual:OutputPage.php

/**
 * Ideas: user-by-user analysis, page-by-page analysis, 
 * 
 * 
 * 
 */
class SpecialWikiStats extends SpecialPage {
    
    function __construct() {
        parent::__construct( 'WikiStats' );
    }
    
    /**
     * Make your magic happen!
     */    
    function execute( $par ) {
        global $wgOut;
        $wgOut->addModuleScripts( 'ext.WikiStats' );
        $wgOut->setPageTitle( wfMsg( 'wikistats-pagetitle' ) );
        $wgOut->addWikiMsg( 'wikistats-welcome' );
        
        $dbr = wfGetDB( DB_SLAVE );
        
        $totalusers = $this->getTotalUsers($dbr);
        $totalpages = $this->getTotalPages($dbr);
        $totalrevisions = $this->getTotalRevisions($dbr);
        $totalbytes = $this->getTotalBytes($dbr);
        $totalfiles = $this->getTotalFiles($dbr);
        $totalvisits = $this->getTotalVisits($dbr);
        
        $wgOut->addWikiMsg( 'wikistats-basic-stats', $totalusers, 0, 0, $totalpages, $totalrevisions, $totalbytes, $totalfiles, $totalvisits );
        
        $sql = "SELECT CONCAT(YEAR(rev_timestamp),'-',LPAD(MONTH(rev_timestamp), 2, 0),'-',LPAD(DAY(rev_timestamp), 2, 0),'T00:00:00Z') AS timestamp, count(rev_id) AS rev_count from revision WHERE 1 GROUP BY timestamp ORDER BY timestamp ASC";
        $res = $dbr->query( $sql );
        $d1 = '';
        foreach ( $res as $row ) {
            $d1 .= '["'.$this->mwtime2unixtime($row->timestamp).'", '.$row->rev_count.'], ';
        }
        $wgOut->addHTML( '<h2>'.wfMsg( 'wikistats-global-summary-header') .'</h2>

<div id="placeholder" style="width:600px;height:300px;"></div>

<script type="text/javascript">
$(function () {
    var d1 = ['.$d1.'];
    
    var placeholder = $("#placeholder");
    var data = [ d1, ];
    var options = { xaxis: { mode: "time" }, lines: { show: true }, points: { show: true }, legend: { noColumns: 1 }, grid: { hoverable: true }, };
    $.plot(placeholder, data, options);
});

</script>' );
        $sql = "SELECT rev_user, rev_user_text, count(rev_id) as rev_count FROM revision WHERE 1 GROUP BY rev_user ORDER BY rev_count DESC LIMIT 25";
        $res = $dbr->query( $sql );
        $wgOut->addHTML( '<h2>'.wfMsg( 'wikistats-users-ranking-header') .'</h2>' );
        $wgOut->addHTML( '<table class="wikitable sortable">' );
        $wgOut->addHTML( '<tr><th>'.wfMsg('wikistats-username-th').'</th><th>'.wfMsg('wikistats-edits-th').'</th></tr>' );
        foreach ( $res as $row ) {
            $wgOut->addHTML( '<tr><td>'.$row->rev_user_text.'</td><td>'.$row->rev_count.'</td></tr>' );
        }
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
