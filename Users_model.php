<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();
    $this->load->database();
    date_default_timezone_set('Asia/Jakarta');
  }

  private function _get_datatables_query($tabel, $searchColumn)
  {
    $column_order = $searchColumn; //set column field database for datatable orderable
    $column_search = $searchColumn;

    $this->db->from($tabel);

    $i = 0;

    foreach ($searchColumn as $item) // loop column 
    {
      if ($_POST['search']['value']) // if datatable send POST for search
      {

        if ($i === 0) // first loop
        {
          $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
          $this->db->like($item, $_POST['search']['value']);
        } else {
          $this->db->or_like($item, $_POST['search']['value']);
        }

        if (count($column_search) - 1 == $i) //last loop
          $this->db->group_end(); //close bracket
      }
      $i++;
    }

    if (isset($_POST['order'])) // here order processing
    {
      $this->db->order_by($column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
    } else if (isset($this->order)) {
      $order = $this->order;
      $this->db->order_by(key($order), $order[key($order)]);
    }
  }

  function get_datatables($tabel, $searchColumn)
  {
    $this->_get_datatables_query($tabel, $searchColumn);
    if ($_POST['length'] != -1)
      $this->db->limit($_POST['length'], $_POST['start']);
    $this->db->where('deleted_at is null');

    $this->db->order_by('user_id', 'desc');
    $query = $this->db->get();

    return $query->result();
  }

  function count_filtered($tabel, $searchColumn)
  {
    $this->_get_datatables_query($tabel, $searchColumn);
    $this->db->where('deleted_at is null');

    $query = $this->db->get();
    return $query->num_rows();
  }

  public function count_all($tabel)
  {
    $this->db->from($tabel);
    $this->db->where('deleted_at is null');

    return $this->db->count_all_results();
  }

  public function get_by_id($nik, $tabel, $idWhere)
  {
    $this->db->from($tabel);
    $this->db->where($idWhere, $nik);
    $query = $this->db->get();

    return $query->row();
  }

  public function save($data, $tabel)
  {
    $this->db->insert($tabel, $data);
    return $this->db->insert_id();
  }

  public function update($tabel, $where, $data)
  {
    $this->db->update($tabel, $data, $where);
    return $this->db->affected_rows();
  }

  public function delete_by_id($id, $idWhere, $tabel)
  {
    $this->db->where($idWhere, $id);
    $this->db->delete($tabel);
  }
}
