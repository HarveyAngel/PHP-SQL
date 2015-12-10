<?php

header("Content-Type: text/html;charset=utf-8");


class SQL{
    private $ConName="localhost";
    private $UserName="root";
    private $ConPwd="";
    private $DbName="English";
    private $con;
    public function __construct(){
        $this->con= new mysqli($this->ConName, $this->UserName, $this->ConPwd, $this->DbName);
    }
    public function close(){
        //$this->con->close();
    }
    public function SqlForDataset($SqlStr){
        $this->con->query("set names 'utf8'");
        $result = $this->con->query($SqlStr);

        $this->close();
        return $result;
    }

    public function SqlForInsert($SqlStr){
        $this->con->query("set names utf8");
        if ($this->con->query($SqlStr)) {
            $id=$this->con->insert_id;
            $this->close();
            return $id;
        } else {
            $this->close();
            return false;
        }
    }

    public function Insert($TableName,$ArrColumn,$ArrValue){
        if(count($ArrColumn)!=count($ArrValue)){
            return false;
        }
        $sql="insert into ".$TableName." (".$this->ArrayToStr($ArrColumn,true).") values"."(".$this->ArrayToStr($ArrValue,false).")";
        return $this->SqlForInsert($sql);
        //return $sql;
    }
    public function Update($TableName,$ArrColumn,$ArrValue,$ArrPoColumn,$ArrPoValue){
        if(count($ArrColumn)!=count($ArrValue)||count($ArrPoColumn)!=count($ArrPoValue)){
            return false;
        }
        $sql="update ".$TableName." set ".$this->ArrayToCon($ArrColumn,$ArrValue,false)." where ".$this->ArrayToCon($ArrPoColumn,$ArrPoValue,true);
        return $this->SqlForInsert($sql);
    }
    public function Select($TableName,$ArrColumn,$ArrPoColumn,$ArrPoValue){
        $sql="select ".$this->ArrayToStr($ArrColumn,true)." from ".$TableName." where ".$this->ArrayToCon($ArrPoColumn,$ArrPoValue,true);
        return $this->SqlForDataset($sql);

    }
    public function SelectAll($TableName,$ArrColumn){
        $sql="select ".$this->ArrayToStr($ArrColumn,true)." from ".$TableName;
        return $this->SqlForDataset($sql);

    }
    public function SelectIn($TableName,$ArrColumn,$ArrPoColumn,$ArrPoValue){
        $sql="select ".$this->ArrayToStr($ArrColumn,true)." from ".$TableName." where ".$ArrPoColumn." IN (".$this->ArrayToStr($ArrPoValue,true).")";
        return $this->SqlForDataset($sql);

    }
    public function Procedures($ProcessName,$Parameter){
        $sql = "call ".$ProcessName."(".$this->ArrayToStr($Parameter,false).");";
        return $this->SqlForDataset($sql);
    }
    public function Delete($TableName,$ArrPoColumn,$ArrPoValue){
        $sql="delete  from ".$TableName." where ".$this->ArrayToCon($ArrPoColumn,$ArrPoValue,true);
        return $this->SqlForDataset($sql);
    }
    public function SelectLimit($TableName,$ArrColumn,$ArrPoColumn,$ArrPoValue,$Top,$Bottom){
        $sql="select ".$this->ArrayToStr($ArrColumn,true)." from ".$TableName." where ".$this->ArrayToCon($ArrPoColumn,$ArrPoValue,true)."  limit ".$Top.",".$Bottom;
        return $this->SqlForDataset($sql);
        /*$Arr=array(
            'answer'=>"ER",
            'num'=>$sql
        );
        echo json_encode($Arr);*/
        //return $sql;
        //echo $sql;die();
    }
    public function SelectOnPage($TableName,$ArrColumn,$ArrPoColumn,$ArrPoValue,$Page,$PageNum){
        $Top=$PageNum*($Page-1);
        $Bottom=$PageNum*$Page;
        return $this->SelectLimit($TableName,$ArrColumn,$ArrPoColumn,$ArrPoValue,$Top,$Bottom);
    }
    public function SelectOrder($TableName,$OrderBy,$Desc,$ArrColumn,$ArrPoColumn,$ArrPoValue){
        $desc="";
        if($Desc){
            $desc=" DESC ";
        }
        $sql="select ".$this->ArrayToStr($ArrColumn,true)." from ".$TableName." where ".$this->ArrayToCon($ArrPoColumn,$ArrPoValue,true)." order by ".$OrderBy.$desc;
        return $this->SqlForDataset($sql);
        //return $sql;
        //echo $sql;die();
    }


    /**
     * @param $TableName
     * @param $ArrPoColumn
     * @param $ArrPoValue
     * @return mixed
     */
    public function SelectPage($TableName,$ArrPoColumn,$ArrPoValue){
        $sql="select count(*) as nums   from ".$TableName." where ".$this->ArrayToCon($ArrPoColumn,$ArrPoValue,true);
        $result=$this->SqlForDataset($sql);
        $row=$result->fetch_assoc();
        return $row["nums"];
    }

    private function ArrayToStr($Array,$switch){
        $sign="";
        $Value="";
        foreach ($Array as &$value) {
            if(is_numeric($value)||$switch){
                $Value=$Value.$sign.$value;
            }else{
                $Value=$Value.$sign."'".$value."'";
            }
            if($sign!=","){
                $sign=",";
            }
        }
        return $Value;
    }
    private function ArrayToCon($ArrColumn,$ArrValue,$switch){//Con means Condition
        $Condition="";
        $And="";
        for($i=0;$i<count($ArrColumn);$i++){
            if($i==1){
                if($switch){
                    $And=" and ";
                }
                else{
                    $And=" , ";
                }
            }
            $last=$ArrColumn[$i][strlen($ArrColumn[$i])-1];
            $Sign="";
            if($last=="<"||$last==">"||$last=="="){

            }else{
                $Sign="=";
            }

            if(is_numeric($ArrValue[$i])){
                $Condition=$Condition.$And.$ArrColumn[$i].$Sign.$ArrValue[$i];
            }else{
                $Condition=$Condition.$And.$ArrColumn[$i].$Sign." '".$ArrValue[$i]."' ";
            }
        }
        return $Condition;
    }
}
