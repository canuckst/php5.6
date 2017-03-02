<?php
class Uniformstyles extends CI_Controller {

  private $title;
  private $trip;

  public function __construct()
  {
    parent::__construct();
    $this->load->library('template');
    $this->load->library('session');
    $this->load->library('pagination');
    $this->load->library('table');
    $this->load->library('form_validation');
    $this->load->library('upload');

    $this->load->helper(array('form', 'url'));
    $this->load->language('properties');

    $this->load->model('styles');
    $this->load->model('codes');
    $this->load->model('items');
    $this->load->model('attributes');

    $this->title = "Uniform Styles";
    $this->trip = '<img src="'.WEB_CONTEXT.'/images/icons/icon_arrow_right.gif" />&nbsp;Uniforms';
    $this->trip = $this->trip.'&nbsp;<img src="'.WEB_CONTEXT.'/images/icons/icon_arrow_right.gif" />&nbsp;Styles';
  }

  public function select($items_start = 0, $itemcode = '', $styles_edit = 0, $message = '')
  {
    if ($this->session->userdata('uf_logged_in')) {
      $uf_orders = $this->session->userdata('uf_orders');
      $uf_stocks = $this->session->userdata('uf_stocks');
      $uf_uniforms = $this->session->userdata('uf_uniforms');

      if ($this->codes->role_uniform_read($uf_uniforms) || $this->codes->role_uniform_update($uf_uniforms)) {
        $data['items_start'] = $items_start;
        $data['itemcode'] = $itemcode;
        $data['styles_edit'] = $styles_edit;

        $data['itemname'] = $this->items->select($itemcode);
        $data['rows'] = $this->styles->select_styles_by_itemcode($itemcode);
        $data['colours'] = $this->attributes->select_color();
        $data['colours_options'] = 'id="colours" size="1"';

        $this->template->write_view('header', 'common/admin_header');
        $this->template->write('message', $message);
        $this->template->write('title', $this->title.' : '.$data['itemname'].' ('.$itemcode.')');
        $this->template->write('trip', $this->trip.'&nbsp;<img src="'.WEB_CONTEXT.'/images/icons/icon_arrow_right.gif" />&nbsp;'.$data['itemname'].' ('.$itemcode.')');

        $table_settings=array(
                            'table_open' => '<table class="zebraTable" width="1000">',
                            'heading_row_start' => '<tr class="rowEven">',
                            'row_start' => '<tr class="rowOdd">',
                            'row_alt_start' => '<tr class="rowEven">'
        );
        $this->table->set_template($table_settings); //apply the settings

        $this->table->set_heading('Style #', 'Colour', 'Front Image', 'Rear Image', 'Side Image', 'Actions', '');

        if ($data['rows']) {
          foreach($data['rows']->result() as $row)
          {
            $edit_col = sprintf('<a href="%s/uniformstyles/select/%d/%s/%d" title="Colour/Image - Edit"><img src="%s/images/buttons/small_edit.gif" /></a>', WEB_CONTEXT, $items_start, (string)$row->itemcode, (string)$row->styleid, WEB_CONTEXT);
            $inventory_col = sprintf('<a href="%s/uniformstocks/inventory/%d/%s/%d" title="Inventory/Stock - List"><img src="%s/images/buttons/button_inventory.jpg" /></a>', WEB_CONTEXT, $items_start, (string)$row->itemcode, $row->styleid, WEB_CONTEXT);
            if ($styles_edit == $row->styleid) {
              $attid = $this->attributes->select_color_by_name($row->colorname);
              if ($attid) {
                $this->attributes->update_color($attid, $row->colorname);
              }
              else {
                $this->attributes->insert_color($row->colorname);
              }
              $data['colours'] = $this->attributes->select_color();
              $update_col = sprintf('<img src="%s/images/icons/icon_save.gif" /><input type="submit" value="Update" title="Update style"/>', WEB_CONTEXT);
              $colorname_col = form_dropdown('update_colour', $data['colours'], ucwords(strtolower($row->colorname)), $data['colours_options']);
              $frontimage_col = sprintf('<input type="file" name="update_frontimage" size="12" />');
              $rearimage_col = sprintf('<input type="file" name="update_rearimage" size="12" />');
              $sideimage_col = sprintf('<input type="file" name="update_sideimage" size="12" />');
              $this->table->add_row($update_col,$colorname_col,$frontimage_col,$rearimage_col,$sideimage_col,$edit_col,$inventory_col);
            } else {
              if ($row->frontimage) {
                $frontimage_col = sprintf('<img src="%s/uploads/%s" width="200px" />', WEB_CONTEXT, $row->frontimage);
              } else {
                $frontimage_col = '';
              }
              if ($row->rearimage != '') {
                $rearimage_col = sprintf('<img src="%s/uploads/%s" width="200px" />', WEB_CONTEXT, $row->rearimage);
              } else {
                $rearimage_col = '';
              }
              if ($row->sideimage != null) {
                $sideimage_col = sprintf('<img src="%s/uploads/%s" width="200px" />', WEB_CONTEXT, $row->sideimage);
              } else {
                $sideimage_col = '';
              }
              $this->table->add_row($row->styleid,$row->colorname,$frontimage_col,$rearimage_col,$sideimage_col,$edit_col,$inventory_col);
            }
          }
        }
        $data['styles_table'] = $this->table->generate();

        $this->template->write_view('content', 'styles', $data);

        // Write to $content
        $this->template->write_view('footer', 'common/footer');
        // Render the template
        $this->template->render();
      }

    } else {
      redirect('/accounts/index/', 'refresh');
    }

  }

  public function update($items_start = 0, $itemcode = '', $styles_edit = 0, $message = '')
  {
    $this->form_validation->set_error_delimiters('<div class="messageStackError">', '</div>');
    $this->form_validation->set_rules('update_colour', 'UpdateColour', 'trim|required|min_length[3]|max_length[50]');
    if ($this->session->userdata('uf_logged_in')) {
      $uf_orders = $this->session->userdata('uf_orders');
      $uf_stocks = $this->session->userdata('uf_stocks');
      $uf_uniforms = $this->session->userdata('uf_uniforms');

      if ($this->codes->role_uniform_update($uf_uniforms)) {
        if ($this->form_validation->run() == TRUE)
        {
          $colorname = $this->input->get_post('update_colour',TRUE);

          $config['upload_path'] = './uploads/';
          $config['allowed_types'] = 'gif|jpg|png';
          $config['overwrite'] = TRUE;
          $config['max_size'] = '0';
          //$config['max_width'] = '1024';
          //$config['max_height'] = '768';
          $config['file_name'] = $itemcode.'-'.$styles_edit.'-frontimage';

          $this->upload->initialize($config);
          if (!$this->upload->do_upload('update_frontimage'))
          {
            $upload = $this->upload->display_errors();
          }
          else
          {
            $upload = $this->upload->data();
            $this->styles->update_frontimage($styles_edit, $upload['file_name']);
          }

          $config['file_name'] = $itemcode.'-'.$styles_edit.'-rearimage';

          $this->upload->initialize($config);
          if (!$this->upload->do_upload('update_rearimage'))
          {
            $upload = $this->upload->display_errors();
          }
          else
          {
            $upload = $this->upload->data();
            $this->styles->update_rearimage($styles_edit, $upload['file_name']);
          }

          $config['file_name'] = $itemcode.'-'.$styles_edit.'-shortimage';

          $this->upload->initialize($config);
          if (!$this->upload->do_upload('update_sideimage'))
          {
            $upload = $this->upload->display_errors();
          }
          else
          {
            $upload = $this->upload->data();
            $this->styles->update_sideimage($styles_edit, $upload['file_name']);
          }

          if ($this->styles->update($styles_edit, $colorname)) {
            $this->select($items_start,$itemcode,0,INFO_SUCCESS);
          } else {
            $this->select($items_start,$itemcode,0,INFO_UNCHANGED);
          }
        } else {
          $this->select($items_start,$itemcode,0,ERROR_FORM_VALIDATION);
        }
      } else {
        $this->select($items_start,$itemcode,0,INFO_UNAUTHORISED);
      }
    } else {
      redirect('/accounts/', 'refresh');
    }

  }

  public function insert_style($items_start = 0, $itemcode = '', $message = '')
  {
    $this->form_validation->set_error_delimiters('<div class="messageStackError">', '</div>');
    $this->form_validation->set_rules('colour', 'Colour', 'trim|required|min_length[3]|max_length[50]');
    if ($this->session->userdata('uf_logged_in')) {
      $uf_orders = $this->session->userdata('uf_orders');
      $uf_stocks = $this->session->userdata('uf_stocks');
      $uf_uniforms = $this->session->userdata('uf_uniforms');
      if ($this->codes->role_uniform_update($uf_uniforms)) {
        if ($this->form_validation->run() == TRUE)
        {
          $colorname = $this->input->get_post('colour',TRUE);
          if ($this->styles->insert($itemcode, $colorname, '', '', '')) {
            $this->select($items_start,$itemcode,0,INFO_SUCCESS);
          } else {
            $this->select($items_start,$itemcode,0,INFO_UNCHANGED);
          }
        } else {
          $this->select($items_start,$itemcode,0,ERROR_FORM_VALIDATION);
        }
      } else {
        $this->select($items_start,$itemcode,0,INFO_UNAUTHORISED);
      }
    } else {
      redirect('/accounts/index/', 'refresh');
    }

  }

  public function insert_color($items_start = 0, $itemcode = '', $message = '')
  {
    $this->form_validation->set_error_delimiters('<div class="messageStackError">', '</div>');
    $this->form_validation->set_rules('colour', 'Colour', 'trim|required|min_length[3]|max_length[50]');
    if ($this->session->userdata('uf_logged_in')) {
      $uf_orders = $this->session->userdata('uf_orders');
      $uf_stocks = $this->session->userdata('uf_stocks');
      $uf_uniforms = $this->session->userdata('uf_uniforms');

      if ($this->codes->role_uniform_update($uf_uniforms)) {
        if ($this->form_validation->run() == TRUE)
        {
          $colorname = $this->input->get_post('colour',TRUE);
          if ($this->attributes->insert_color($colorname)) {
            $this->select($items_start,$itemcode,0,INFO_SUCCESS);
          } else {
            $this->select($items_start,$itemcode,0,INFO_UNCHANGED);
          }
        } else {
          $this->select($items_start,$itemcode,0,ERROR_FORM_VALIDATION);
        }
      } else {
        $this->select($items_start,$itemcode,0,INFO_UNAUTHORISED);
      }
    } else {
      redirect('/accounts/index/', 'refresh');
    }

  }

  public function comments()
  {
    echo 'Uniform Styles - maintenance.';
  }

}
?>
