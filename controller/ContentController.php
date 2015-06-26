<?
require_once('Controller.php');
require_once('HomeController.php');
require_once('AdminController.php');
require_once('model/Content.php');
require_once('model/BaseModel.php');
require_once('model/Block.php');
require_once('lib/ArrayUtil.php');
require_once('lib/StringUtil.php');
require_once('lib/LogUtil.php');

class ContentController extends Controller{

	private  $content = null;
	private  $block = null;

	function __construct(){
		parent::__construct();
		$this->content = new Content();
		$this->block = new Block();
	}


	function listPage(){

		$postTile = "";
		$postBlockName = "";
		$sts = "1,-1";

		if(isset($_POST['title']))
		{
			$postTile = $_POST["title"];
		}
		if(isset($_POST['sts']))
		{
			$sts = $_POST["sts"];
		}
		if(isset($_POST['block_name']))
		{
			$postBlockName = $_POST["block_name"];
		}

		$table = "(select a.*,b.block_name,c.usr_nm from content a left join block b on a.block_id=b.block_id left join users c on a.usr_id=c.usr_id where a.sts in (".formatString($sts).") and a.title like '%".$postTile."%' and b.block_name like '%".$postBlockName."%' order by edit_tm desc) mytable";
		$pager = parent::getPager($table,$this->content->db);
		$arrayList = $pager->getData();
		$blockList = $this->block->getBlocks();
		require('view/admin/content_list.php');
	}

	function del(){
		$this->content->del($_POST["id"]);
		$adminController = new AdminController();
		$adminController->publishStaticIndex("1");
	}

	function recommend(){
		$this->content->recommend($_POST["id"],$_POST["opr"]);
		echo "true";
	}

	function saveRss(){
		//include "config/site.php";
		$curTm = date("YmdHis",time());
		if(empty($_POST["id"])){
			$_POST["input_tm"] = $curTm;
		}
		$_POST["edit_tm"] = $curTm;
		$_POST["dsp_img"] = '';
		$_POST['usr_id'] = $_SESSION['loginuser']['usr_id'];
		
		
		
		//默认情况POST字段中的单引号会被自动转义，但通过base64编码后，无法转义，需手动处理
		$_POST["title"] = addslashes(base64_decode($_POST["title"]));
		
		$_POST["content"]= base64_decode($_POST["content"]);
		$_POST["dsp_img"] = $this->getRsstImg($_POST["content"]);
		
		$_POST["content"]= addslashes($_POST["content"]);
		
		$_POST["pub_tm"]= addslashes(base64_decode($_POST["pub_tm"]));
		$_POST["link"]= addslashes(base64_decode($_POST["link"]));
	    
		
		if(!isset($_POST["keyword"])){
			$_POST["keyword"] = $_POST["title"];
		}
		
		$_POST["content_short"] = plainSubText($_POST["content"],200);

		$this->content->save();
		echo "true";
		
		//$adminController = new AdminController();
		//$adminController->publishStaticIndex();
	}
	
	function save(){
		//include "config/site.php";
		$curTm = date("YmdHis",time());
		if(empty($_POST["id"])){
			$_POST["input_tm"] = $curTm;
		}
		$_POST["edit_tm"] = $curTm;
		
		$dsp_img = $this->getFirstImg($_POST["content"]);
		$_POST["dsp_img"] = $dsp_img;
		
		//$logger = LogUtil::getLogger();
		//$logger->info($_POST["content"]);
		//$logger->info($dsp_img);
		
		$_POST['usr_id'] = $_SESSION['loginuser']['usr_id'];
		
		if(!isset($_POST["keyword"])){
			$_POST["keyword"] = $_POST["title"];
		}
		
		$_POST["content_short"] = plainSubText($_POST["content"],200);

		$this->content->save();
		
		//if ( $_POST["sts"] == "1") {
		    $adminController = new AdminController();
		    $adminController->publishOne($_POST["id"],$_POST["block_id"]);
		//}
		
		
	}
	
	function editPage(){
		$blockList = $this->block->getArrayList("block");
		$obj = $this->content->getRecordById($_GET['id']);
		require('view/admin/content_edit.php');
	}

	
	public function getRsstImg($str) {
	    
		$pattern="/<img src=\"(.*?)\"\/>/i";
        if(preg_match($pattern, $str,$out)){
           return $out[1];
        }
		return "";
	}
	
	
	public function getFirstImg($str) { 
		$pattern="/<img src=\\\\\"(.*?)\\\\\"/i";   //<img src=\"upload/32d3ca5e23f4ccf1e4c8660c40e75f33.png\" style=\"width: 114px;\"></p> 
        if(preg_match($pattern, $str,$out)){
           return $out[1];
        }
		return "";
	}
	
}
/*end*/