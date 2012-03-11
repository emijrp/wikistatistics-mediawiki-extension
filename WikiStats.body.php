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
        global $wgLang, $wgOut;
        $wgOut->addModuleScripts( 'ext.WikiStats' );
        $wgOut->setPageTitle( wfMsg( 'wikistats-pagetitle' ) );
        $wgOut->addWikiMsg( 'wikistats-welcome' );
        
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
        
        $wgOut->addHTML( '<h2>'.wfMsg( 'wikistats-global-summary-header') .'</h2>');
        $wgOut->addWikiMsg( 'wikistats-basic-stats', $wgLang->formatNum($totalusers), $wgLang->formatNum(0), $wgLang->formatNum(0), $wgLang->formatNum($totalpages), $wgLang->formatNum($totalrevisions), $wgLang->formatNum($totalbytes), $wgLang->formatNum($totalfiles), $wgLang->formatNum($totalvisits) );
        
        $sql = "SELECT CONCAT(YEAR(rev_timestamp),'-',LPAD(MONTH(rev_timestamp), 2, 0),'-',LPAD(DAY(rev_timestamp), 2, 0),'T00:00:00Z') AS timestamp, count(rev_id) AS rev_count from revision WHERE 1 GROUP BY timestamp ORDER BY timestamp ASC";
        $res = $dbr->query( $sql );
        $d1 = '';
        foreach ( $res as $row ) {
            $d1 .= '["'.$this->mwtime2unixtime($row->timestamp).'", '.$row->rev_count.'], ';
        }
        $dbr->freeResult( $res );
        
        $wgOut->addHTML( '<h2>'.wfMsg( 'wikistats-global-activity-header') .'</h2>');
        $wgOut->addHTML( '<h3>'.wfMsg( 'wikistats-global-activity-all-header') .'</h3>');
        
        $wgOut->addHTML( '<div id="placeholder" style="width:'.$graphwidth.';height:'.$graphheight.';"></div>

<script type="text/javascript">
$(function () {
    var d1 = ['.$d1.'];
    
    var placeholder = $("#placeholder");
    var data = [ d1, ];
    var options = { xaxis: { mode: "time" }, lines: { show: true }, points: { show: true }, legend: { noColumns: 1 }, grid: { hoverable: true }, };
    $.plot(placeholder, data, options);
});

</script>' );
        
        $wgOut->addHTML( '<h3>'.wfMsg( 'wikistats-global-activity-hourly-header') .'</h3>');
        $wgOut->addHTML( '<h3>'.wfMsg( 'wikistats-global-activity-daily-header') .'</h3>');
        $wgOut->addHTML( '<h3>'.wfMsg( 'wikistats-global-activity-weekly-header') .'</h3>');
        $wgOut->addHTML( '<h3>'.wfMsg( 'wikistats-global-activity-monthly-header') .'</h3>');

        $sql = "SELECT rev_user, rev_user_text, count(rev_id) as rev_count FROM revision WHERE 1 GROUP BY rev_user ORDER BY rev_count DESC LIMIT " . $limit;
        $res = $dbr->query( $sql );
        $wgOut->addHTML( '<h2>'.wfMsg( 'wikistats-users-ranking-header') .'</h2>' );
        $wgOut->addHTML( '<table class="wikitable sortable">' );
        $wgOut->addHTML( '<tr><th>'.wfMsg('wikistats-username-th').'</th><th>'.wfMsg('wikistats-edits-th').'</th><th>%</th></tr>' );
        foreach ( $res as $row ) {
            $wgOut->addHTML( '<tr><td>'.Linker::userLink( $row->rev_user, $row->rev_user_text).'</td><td>'.$row->rev_count.'</td><td>'.round($row->rev_count/($totalrevisions/100), 2).'%</td></tr>' );
        }
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
