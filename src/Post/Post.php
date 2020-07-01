<?php


namespace Momo\Weibot\Post;

use Momo\Weibot\Http\Http;
use Momo\Weibot\Models\SendResModel;

/**
 * 微博操作
 * Class Post
 * @package Momo\Weibot\Post
 */
class Post
{
    /**
     * 获取毫秒
     * @return string
     */
    protected function micotime() :string {
        return time().rand(100,999);
    }

    /**
     * 操作统一返回
     * @param $html
     * @return SendResModel
     */
    protected function result($html){
        $body = json_decode($html,true);
        $res = is_array($body) && $body["code"] == "100000" ? true : false;
        return new SendResModel($res ? true : false,$res ? "发送成功" : (!empty($body["msg"]) ? $body["msg"] : "未知错误"));
    }

    /**
     * 发微博
     * @param $text string 微博内容
     * @return SendResModel
     */
    public function send(string $text) :SendResModel{
        $rt = Http::getClient()->post("https://weibo.com/aj/mblog/add?ajwvr=6&__rnd=".$this->micotime(),[
            'form_params' => [
                'location' => "v6_content_home",
                'text' => $text,
                'appkey' => "",
                'style_type' => "1",
                'pic_id' => "",
                'tid' => "",
                'pdetail' => "",
                'mid' => "",
                'isReEdit' => "false",
                'rank' => "0",
                'rankid' => "",
                'module' => "stissue",
                'pub_source' => "main_",
                'pub_type' => "dialog",
                'isPri' => "0",
                '_t' => "0",
            ]
        ]);
        return $this->result($rt->getBody()->getContents());
    }

    /**
     * 评论
     * @param string $uniqueId 用户uid
     * @param string $mid 微博ID
     * @param string $content 评论内容
     * @param int $forward 是否转发到自己微博
     * @return SendResModel
     */
    public function comment(string $uniqueId , string $mid, string $content, int $forward = 0) :SendResModel
    {
        $rt = Http::getClient()->post('https://weibo.com/aj/v6/comment/add?ajwvr=6&__rnd='.$this->micotime(), [
            'form_params' => [
                "act"=>"post",
                "mid"=>$mid,
                "uid"=>$uniqueId,
                "forward"=>$forward,
                "isroot"=>"0",
                "content"=>$content,
                "location"=>"page_100505_home",
                "module"=>"scommlist",
                "group_source"=>"",
                "pdetail"=>"1005055370852522",
                "_t"=>"0",
            ]
        ]);
        return $this->result($rt->getBody()->getContents());
    }

    /**
     * 转发
     * @param string $mid 微博id
     * @param string $reason 转发原因
     * @param int $isCommentBase 是否对原微博评论
     * @param int $isComment 是否评论
     * @return SendResModel
     */
    public function forward(string $mid,string $reason,int $isCommentBase = 0,int $isComment = 0) :SendResModel
    {
        $rt = Http::getClient()->post('https://weibo.com/aj/v6/mblog/forward?ajwvr=6&domain=100505&__rnd='.$this->micotime(), [
            'form_params' => [
                "pic_src"=>"",
                "pic_id"=>"",
                "appkey"=>"",
                "mid"=>$mid,
                "style_type"=>1,
                "mark"=>"",
                "reason"=>$reason,
                "location"=>"page_100505_home",
                "pdetail"=>"1005055370852522",
                "module"=>"",
                "page_module_id"=>"",
                "refer_sort"=>"",
                "is_comment_base"=>$isCommentBase,
                "is_comment"=>$isComment,
                "rank"=>"0",
                "rankid"=>"",
                "isReEdit"=>"false",
            ]
        ]);
        return $this->result($rt->getBody()->getContents());
    }

    /**
     * 删除微博
     * @param string $mid 微博id
     * @return SendResModel
     */
    public function delete(string $mid) :SendResModel
    {
        $rt = Http::getClient()->post('https://weibo.com/aj/mblog/del?ajwvr=6', [
            'form_params' => [
                'mid' => $mid,
            ]
        ]);
        return $this->result($rt->getBody()->getContents());
    }

}