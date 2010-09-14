<?php
// $Id: makepdf.php,v 4.04 2008/06/05 15:58:07 wishcraft Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License 2.0 as published by //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
//  Author: wishcraft (S.F.C., sales@chronolabs.org.au)                      //
//  URL: http://www.chronolabs.org.au/forums/X-Forum/0,17,0,0,100,0,DESC,0   //
//  Project: X-Forum 4                                                       //
//  ------------------------------------------------------------------------ //

error_reporting(0);
include 'header.php';
require XOOPS_ROOT_PATH.'/modules/xforum/fpdf/fpdf.inc.php';
error_reporting(0);

if(empty($_POST["pdf_data"])){
	
$xforum = isset($_GET['forum']) ? intval($_GET['forum']) : 0;
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;
$post_id = !empty($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if ( empty($post_id) )  die(_MD_ERRORTOPIC);

$post_handler =& xoops_getmodulehandler('post', 'xforum');
$post = & $post_handler->get($post_id);
if(!$approved = $post->getVar('approved'))    die(_MD_NORIGHTTOVIEW);

$post_data = $post_handler->getPostForPDF($post);

$topic_handler =& xoops_getmodulehandler('topic', 'xforum');
$xforumtopic =& $topic_handler->getByPost($post_id);
$topic_id = $xforumtopic->getVar('topic_id');
if(!$approved = $xforumtopic->getVar('approved'))    die(_MD_NORIGHTTOVIEW);

$forum_handler =& xoops_getmodulehandler('forum', 'xforum');
$xforum = ($xforum)?$xforum:$xforumtopic->getVar('forum_id');
$viewtopic_forum =& $forum_handler->get($xforum);
if (!$forum_handler->getPermission($viewtopic_forum))    die(_MD_NORIGHTTOACCESS);
if (!$topic_handler->getPermission($viewtopic_forum, $xforumtopic->getVar('topic_status'), "view"))   die(_MD_NORIGHTTOVIEW);
//if ( !$xforumdata =  $topic_handler->getViewData($topic_id, $xforum) )die(_MD_FORUMNOEXIST);

$pdf_data['title'] = $viewtopic_forum->getVar("forum_name");
$pdf_data['subtitle'] = $xforumtopic->getVar('topic_title');
$pdf_data['subsubtitle'] = $post_data['subject'];
$pdf_data['date'] = $post_data['date'];
$pdf_data['content'] = $post_data['text'];
$pdf_data['author'] = $post_data['author'];

}else{
	$pdf_data = unserialize(base64_decode($_POST["pdf_data"]));
}

$pdf_data['filename'] = preg_replace("/[^0-9a-z\-_\.]/i",'', $pdf_data["title"]);
$pdf_data['title'] = forum_PDF_SUBJECT.': '.$pdf_data["title"];
if (!empty($pdf_data['subtitle'])){
	$pdf_data['subtitle'] = forum_PDF_TOPIC.': '.$pdf_data['subtitle'];
}
$pdf_data['author'] = forum_PDF_AUTHOR.': '.$pdf_data['author'];
$pdf_data['date'] = forum_PDF_DATE. ': '.$pdf_data['date'];
$pdf_data['url'] = URL. ': '.$pdf_data['url'];

//Other stuff
$puff='<br />';
$puffer='<br />';

//create the A4-PDF...
$pdf=new PDF();
if(method_exists($pdf, "encoding")){
	$pdf->encoding($pdf_data, _CHARSET);
	$pdf->encoding($pdf_config, _CHARSET);
}
$pdf->SetCreator($pdf_config['creator']);
$pdf->SetTitle($pdf_data['title']);
$pdf->SetAuthor($pdf_config['url']);
$pdf->SetSubject($pdf_data['author']);
$out=$pdf_config['url'].', '.$pdf_data['author'].', '.$pdf_data['title'].', '.$pdf_data['subtitle'];
$pdf->SetKeywords($out);
$pdf->SetAutoPageBreak(true,25);
$pdf->SetMargins($pdf_config['margin']['left'],$pdf_config['margin']['top'],$pdf_config['margin']['right']);
$pdf->Open();

//First page
$pdf->AddPage();
$pdf->SetXY(24,25);
$pdf->SetTextColor(10,60,160);
$pdf->SetFont($pdf_config['font']['slogan']['family'],$pdf_config['font']['slogan']['style'],$pdf_config['font']['slogan']['size']);
$pdf->WriteHTML($pdf_config['slogan'], $pdf_config['scale']);
$pdf->Image($pdf_config['logo']['path'],$pdf_config['logo']['left'],$pdf_config['logo']['top'],$pdf_config['logo']['width'],$pdf_config['logo']['height'],'',$pdf_config['url']);
$pdf->Line(25,30,190,30);
$pdf->SetXY(25,35);
$pdf->SetFont($pdf_config['font']['title']['family'],$pdf_config['font']['title']['style'],$pdf_config['font']['title']['size']);
$pdf->WriteHTML($pdf_data['title'],$pdf_config['scale']);

if (!empty($pdf_data['subtitle'])){
	$pdf->WriteHTML($puff,$pdf_config['scale']);
	$pdf->SetFont($pdf_config['font']['subtitle']['family'],$pdf_config['font']['subtitle']['style'],$pdf_config['font']['subtitle']['size']);
	$pdf->WriteHTML($pdf_data['subtitle'],$pdf_config['scale']);
}
if (!empty($pdf_data["subsubtitle"])) {
	$pdf->WriteHTML($puff,$pdf_config["scale"]);
	$pdf->SetFont($pdf_config["font"]["subsubtitle"]["family"],$pdf_config["font"]["subsubtitle"]["style"],$pdf_config["font"]["subsubtitle"]["size"]);
	$pdf->WriteHTML($pdf_data["subsubtitle"],$pdf_config["scale"]);
}

$pdf->WriteHTML($puff,$pdf_config['scale']);
$pdf->SetFont($pdf_config['font']['author']['family'],$pdf_config['font']['author']['style'],$pdf_config['font']['author']['size']);
$pdf->WriteHTML($pdf_data['author'],$pdf_config['scale']);
$pdf->WriteHTML($puff,$pdf_config['scale']);
$pdf->WriteHTML($pdf_data['date'],$pdf_config['scale']);
$pdf->WriteHTML($puff,$pdf_config['scale']);
$pdf->WriteHTML($pdf_data['url'],$pdf_config['scale']);
$pdf->WriteHTML($puff,$pdf_config['scale']);

$pdf->SetTextColor(0,0,0);
$pdf->WriteHTML($puffer,$pdf_config['scale']);

$pdf->SetFont($pdf_config['font']['content']['family'],$pdf_config['font']['content']['style'],$pdf_config['font']['content']['size']);
$pdf->WriteHTML($pdf_data['content'],$pdf_config['scale']);

$pdf->Output();
?>