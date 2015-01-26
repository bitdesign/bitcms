<?
class HomeController{
	
	function indexDyn($viewPage="index.php",$pubMod=true){
		
		require_once('model/Content.php');
		require_once('ui/Pager.php');
		include "config/site.php";
		$logo = "$webroot/upload/$logo";
		$headimg = "$webroot/upload/$headimg";
		$content = new Content();
		
		$condition = "";
		if( isset ( $_GET['block_id'])){
			$condition .= "and a.block_id=".$_GET['block_id'];
		}
		$indexMaxNum = $homemaxnum;
		$table = "(select id,a.block_id,block_name,title,a.dsp_img,a.edit_tm,usr_nm,visits,top_tm,content_short as content from content a left join users b on a.usr_id=b.usr_id left join block c on a.block_id = c.block_id where sts='1' ".$condition." order by top_tm desc ,pub_tm desc ) tmp";
		$pager = $this->getPager($table,$content->db,$indexMaxNum);
		$contents = $pager->getData();
		require("$tpl_root/$viewPage");
	}
	
	function info(){
		
		require_once('model/Content.php');
		require_once('ui/Pager.php');
		require_once('model/Reply.php');
		include "config/site.php";
		
		$logo = "$webroot/upload/$logo";
		$content = new Content();	
		$contentId = $_GET['id'];
		$obj = $content->getRecordById($contentId);
			
		$table = "(select a.*  from replies a where par_id=".$contentId." order by input_tm desc ) mytable";
		$reply = new Reply(); 
		$replyPegeNum = $commentdspnum;
		$pager = $this->getPager($table,$reply->db,$replyPegeNum);
		$msgList = $pager->getData();
		
		require("$tpl_root/info.php");
	}
	
	function news(){
		include "config/site.php";
		require_once('model/Content.php');
		require_once('model/Block.php');
		$content = new Content();	
		$block = new Block();	
		
		$contentsNewTop = $content->getFirstBatchByTime($newmaxnum);
		$contentsVisitsTop = $content->getFirstBatchByVisits($topmaxnum);
		$blocks = $block->getBlocksWithContentNum();
		require("$tpl_root/_news.php");
	}
	
	function getPager($table,$db,$perPageCnt=50){
		$pageNo = trim($_POST['skipValue']);
		$pager = new Pager($table,$db,$pageNo,$perPageCnt);
		return $pager;
	}
	function index(){
		require("index.html");
	}
	
	
}
/*end*/