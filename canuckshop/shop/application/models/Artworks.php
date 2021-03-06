<?php

class Artworks extends CI_Model {

  function __construct()
  {
    parent::__construct();
    $this->load->database();
    $this->load->library('session');
  }

  function select_artworks_by_order($orderid)
  {
    $select_sql = "SELECT * FROM artworks";
    $select_sql = $select_sql." WHERE orderid = ".$orderid;
    $select_sql = $select_sql." ORDER BY artworksource, artworkid DESC";

    //log_message('debug', $select_sql);
    $query = $this->db->query($select_sql);
    if ($query->num_rows() > 0)
    {
      return $query;
    } else {
      return FALSE;
    }
  }

  function select_customer_artworks_by_order($orderid)
  {
    $select_sql = "SELECT * FROM artworks";
    $select_sql = $select_sql." WHERE orderid = ".$orderid;
    $select_sql = $select_sql." AND artworkstatus <= '1'";
    $select_sql = $select_sql." ORDER BY artworksource, artworkid DESC";
  
    //log_message('debug', $select_sql);
    $query = $this->db->query($select_sql);
    if ($query->num_rows() > 0)
    {
      return $query;
    } else {
      return FALSE;
    }
  }
  
  
  function select_artworks_by_stock($stockid)
  {
    $select_sql = "SELECT * FROM artworks";
    $select_sql = $select_sql." WHERE stockid = ".$stockid;
    $select_sql = $select_sql." ORDER BY artworksource, artworkid DESC";
  
    //log_message('debug', $select_sql);
    $query = $this->db->query($select_sql);
    if ($query->num_rows() > 0)
    {
      return $query;
    } else {
      return FALSE;
    }
  }
  
  function select_artwork_by_id($artworkid)
  {
    $select_sql = "SELECT * FROM artworks";
    $select_sql = $select_sql." WHERE artworkid = ".$artworkid;
    $select_sql = $select_sql." LIMIT 1";
  
    //log_message('debug', $select_sql);
    $query = $this->db->query($select_sql);
    if ($query->num_rows() > 0)
    {
      $row = $query->row();
      return $row;
    } else {
      return FALSE;
    }
  }
  
  function insert_artwork($artworksource, $artworkstatus, $comment, $orderid, $stockid, $uploadby)
  {
    $artworkid = $this->next_artworkid();
    if ($artworkid) {
      $insert_sql="INSERT INTO artworks (artworkid,artworksource,artworkstatus,comment,orderid,stockid,uploadby,uploaddate) VALUES ("
      .$artworkid.",'".$artworksource."','".$artworkstatus."','".$comment."',".$orderid.",".$stockid.",'".$uploadby."',now())";
      $query = $this->db->query($insert_sql);
      if ($this->db->affected_rows() > 0) {
        return $artworkid;
      } else {
        return FALSE;
      }
    } else {
      return FALSE;
    }
  }

  function next_artworkid()
  {
    $select_sql="SELECT MAX(artworkid)+1 AS next_id FROM artworks";
    $query = $this->db->query($select_sql);
    if ($query->num_rows() > 0)
    {
      $row = $query->row();
      if (isset($row->next_id) && $row->next_id != '') 
        return $row->next_id;
      else 
        return 1;
    } else {
      return 1;
    }
  }

  function update_artwork($artworkid, $artworksource, $artworkstatus, $comment, $uploadby)
  {
    $update_sql="UPDATE artworks SET artworksource='".$this->db->escape($artworksource)
    ."',artworkstatus='".$this->db->escape($artworkstatus)
    ."',comment='".$this->db->escape($comment)
    ."',uploadby='".$this->db->escape($uploadby)
    ."' WHERE artworkid = ".$artworkid;

    $query = $this->db->query($update_sql);
    if ($this->db->affected_rows() > 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  function update_filename($artworkid, $filename)
  {
    $update_sql="UPDATE artworks SET filename='".$filename."' WHERE artworkid = ".$artworkid;
    $query = $this->db->query($update_sql);
    if ($this->db->affected_rows() > 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }


}
