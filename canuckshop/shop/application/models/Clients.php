<?php
class Clients extends CI_Model {
	function __construct() {
		parent::__construct ();
		$this->load->database ();
		$this->load->library ( 'session' );
	}
	function search($what) {
		$what = $this->db->escape ( $what );
		$search_sql = "SELECT * FROM clients";
		if (isset ( $what )) {
			$search_sql = $search_sql . " WHERE name like '" . $what . "%'";
			$search_sql = $search_sql . " OR team like '%" . $what . "%'";
			$search_sql = $search_sql . " OR contact like '%" . $what . "%'";
			$search_sql = $search_sql . " OR email like '%" . $what . "%'";
			$search_sql = $search_sql . " OR telephone like '%" . $what . "%'";
			$search_sql = $search_sql . " OR cellphone like '%" . $what . "%'";
		}
		$search_sql = $search_sql . " ORDER BY clientid DESC LIMIT 500";
		
		// log_message('debug', $search_sql);
		$query = $this->db->query ( $search_sql );
		if ($this->db->count_all_results() > 0) {
			return $query;
		} else {
			return FALSE;
		}
	}
	function select($clientid) {
		$select_sql = "SELECT * FROM clients";
		$select_sql = $select_sql . " WHERE clientid = " . $clientid . " LIMIT 1";
		
		$query = $this->db->query ( $select_sql );
		if ($query->num_rows () > 0) {
			$row = $query->row ();
			return $row;
		} else {
			return FALSE;
		}
	}
	function select_client_by_contact_telephone($contact, $telephone) {
		$select_sql = "SELECT * FROM clients";
		$select_sql = $select_sql . " WHERE LOWER(contact) = '" . strtolower ( $contact ) . "' AND telephone = '" . $telephone . "' LIMIT 1";
		$query = $this->db->query ( $select_sql );
		if ($query->num_rows () > 0) {
			$row = $query->row ();
			return $row;
		} else {
			$select_sql = "SELECT * FROM clients";
			$select_sql = $select_sql . " WHERE telephone = '" . $telephone . "' LIMIT 1";
			$query = $this->db->query ( $select_sql );
			if ($query->num_rows () > 0) {
				$row = $query->row ();
				return $row;
			} else {
				return FALSE;
			}
		}
	}
	function insert($name, $team, $contact, $email, $telephone, $cellphone, $fax, $street, $city, $province, $zipcode, $account = '') {
		$clientid = $this->next_clientid ();
		if ($clientid) {
			$insert_sql = "INSERT INTO clients (clientid,name,team,contact,email,account,telephone,cellphone,fax,street,city,province,zipcode,status) VALUES (" . $clientid . ",'" . $this->db->escape ( $name ) . "','" . $this->db->escape ( $team ) . "','" . $this->db->escape ( $contact ) . "','" . $this->db->escape ( $email ) . "','" . $account . "','" . $this->db->escape ( $telephone ) . "','" . $this->db->escape ( $cellphone ) . "','" . $this->db->escape ( $fax ) . "','" . $this->db->escape ( $street ) . "','" . $this->db->escape ( $city ) . "','" . $this->db->escape ( $province ) . "','" . $this->db->escape ( $zipcode ) . "',1)";
			$query = $this->db->query ( $insert_sql );
			if ($this->db->affected_rows () > 0) {
				return $clientid;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
	function next_clientid() {
		$select_sql = "SELECT MAX(clientid)+1 AS next_id FROM clients";
		$query = $this->db->query ( $select_sql );
		if ($query->num_rows () > 0) {
			$row = $query->row ();
			return $row->next_id;
		} else {
			return FALSE;
		}
	}
	function update($clientid, $name, $team, $contact, $email, $telephone, $cellphone, $fax, $street, $city, $province, $zipcode, $account) {
		$update_sql = "UPDATE clients SET name='" . $this->db->escape ( $name ) . "',team='" . $this->db->escape ( $team ) . "',contact='" . $this->db->escape ( $contact ) . "',email='" . $this->db->escape ( $email ) . "',account='" . $this->db->escape ( $account ) . "',telephone='" . $this->db->escape ( $telephone ) . "',cellphone='" . $this->db->escape ( $cellphone ) . "',fax='" . $this->db->escape ( $fax ) . "',street='" . $this->db->escape ( $street ) . "',city='" . $this->db->escape ( $city ) . "',province='" . $this->db->escape ( $province ) . "',zipcode='" . $this->db->escape ( $zipcode ) . "' WHERE clientid = " . $clientid;
		$query = $this->db->query ( $update_sql );
		if ($this->db->affected_rows () > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	function update_client($clientid, $name, $team, $contact, $email, $telephone, $cellphone, $fax, $street, $city, $province, $zipcode) {
		$update_sql = "UPDATE clients SET name='" . $this->db->escape ( $name ) . "',team='" . $this->db->escape ( $team ) . "',contact='" . $this->db->escape ( $contact ) . "',email='" . $this->db->escape ( $email ) . "',telephone='" . $this->db->escape ( $telephone ) . "',cellphone='" . $this->db->escape ( $cellphone ) . "',fax='" . $this->db->escape ( $fax ) . "',street='" . $this->db->escape ( $street ) . "',city='" . $this->db->escape ( $city ) . "',province='" . $this->db->escape ( $province ) . "',zipcode='" . $this->db->escape ( $zipcode ) . "' WHERE clientid = " . $clientid;
		$query = $this->db->query ( $update_sql );
		if ($this->db->affected_rows () > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	function status($clientid, $status) {
		$update_sql = "UPDATE clients SET status=" . $this->db->escape ( $status ) . " WHERE clientid = " . $clientid;
		$query = $this->db->query ( $update_sql );
		if ($this->db->affected_rows () > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
