<?php

class Items extends CI_Model {

  function __construct()
  {
    parent::__construct();
    $this->load->database();
    $this->load->library('session');
  }

  function search($what)
  {
    $what = $this->db->escape($what);
    $search_sql = "SELECT * FROM items";
    if (isset($what)) {
      $search_sql = $search_sql." WHERE itemcode like '".$what."%'";
      $search_sql = $search_sql." OR itemname like '%".$what."%'";
    }
    $search_sql = $search_sql." ORDER BY itemcode LIMIT 1000";

    $query = $this->db->query($search_sql);
    if ($query->num_rows() > 0)
    {
      return $query;
    } else {
      return FALSE;
    }
  }

  function select_online_items()
  {
    $select_sql = "SELECT itemid, itemcode, itemname FROM items";
    $select_sql = $select_sql." WHERE status = true AND online = true";
    $select_sql = $select_sql." ORDER BY itemcode LIMIT 1000";
  
    $query = $this->db->query($select_sql);
    if ($query->num_rows() > 0)
    {
      return $query;
    } else {
      return FALSE;
    }
  }
  
  function select($itemcode)
  {
    $select_sql = "SELECT * FROM items";
    $select_sql = $select_sql." WHERE itemcode = '".$itemcode."' LIMIT 1";

    $query = $this->db->query($select_sql);
    if ($query->num_rows() > 0)
    {
      $row = $query->row();
      return $row->itemname;
    } else {
      return FALSE;
    }
  }

  function select_itemname($itemcode)
  {
    $select_sql = "SELECT * FROM items";
    $select_sql = $select_sql." WHERE itemcode = '".$itemcode."' LIMIT 1";

    $query = $this->db->query($select_sql);
    if ($query->num_rows() > 0)
    {
      $row = $query->row();
      return $row->itemname;
    } else {
      return FALSE;
    }
  }

  function select_code_items()
  {
    $select_sql = "SELECT itemcode, CONCAT(itemcode, ' - ', itemname) as name FROM items";
    $select_sql = $select_sql." WHERE status = '1'";
    $select_sql = $select_sql." ORDER BY itemcode";

    //log_message('debug', $select_sql);
    $query = $this->db->query($select_sql);
    $codes = array();
    $codes['NONE'] = 'Select item below:';

    foreach($query->result() as $row)
    {
      $codes[$row->itemcode] = $row->name;
    }
    return ($codes);

  }

  function insert($itemcode, $itemname)
  {
    log_message('debug', '##insert item');
    $itemid = $this->next_itemid();
    if ($itemid) {
      $insert_sql="INSERT INTO items (itemid,itemcode,itemname) VALUES ("
      .$itemid.",'".$this->db->escape($itemcode)."','".$this->db->escape($itemname)."')";
      $query = $this->db->query($insert_sql);
      if ($this->db->affected_rows() > 0) {
        return TRUE;
      } else {
        return FALSE;
      }
    } else {
      return FALSE;
    }
  }

  function next_itemid()
  {
    $select_sql="SELECT MAX(itemid)+1 AS next_id FROM items";
    $query = $this->db->query($select_sql);
    if ($query->num_rows() > 0)
    {
      $row = $query->row();
      return $row->next_id;
    } else {
      return FALSE;
    }
  }

  function update($itemid, $itemcode, $itemname)
  {
    $update_sql="UPDATE items SET itemcode='".$this->db->escape($itemcode)."',itemname='".$this->db->escape($itemname)
    ."' WHERE itemid = ".$itemid;
    $query = $this->db->query($update_sql);
    if ($this->db->affected_rows() > 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  function delete($itemid)
  {
    $delete_sql="DELETE FROM items WHERE itemid = ".$itemid;
    $query = $this->db->query($delete_sql);
    if ($this->db->affected_rows() > 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  function online($itemid, $online)
  {
    $update_sql="UPDATE items SET online=".$this->db->escape($online)
    ." WHERE ITEMID = ".$itemid;
    $query = $this->db->query($update_sql);
    if ($this->db->affected_rows() > 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  function status($itemid, $status)
  {
    $update_sql="UPDATE items SET status=".$this->db->escape($status)
    ." WHERE ITEMID = ".$itemid;
    $query = $this->db->query($update_sql);
    if ($this->db->affected_rows() > 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }




}
