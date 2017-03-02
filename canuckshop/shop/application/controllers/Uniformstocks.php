<?php
class Uniformstocks extends CI_Controller {

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
    $this->load->model('stocks');

    $this->title = "Uniform Stocks";
    $this->trip = '<img src="'.WEB_CONTEXT.'/images/icons/icon_arrow_right.gif" />&nbsp;Uniforms';
    $this->trip = $this->trip.'&nbsp;<img src="'.WEB_CONTEXT.'/images/icons/icon_arrow_right.gif" />&nbsp;Stocks';

  }

  public function select($items_start = 0, $itemcode = '', $styleid = 0, $message = '')
  {
    if ($this->session->userdata('uf_logged_in')) {
      $uf_orders = $this->session->userdata('uf_orders');
      $uf_stocks = $this->session->userdata('uf_stocks');
      $uf_uniforms = $this->session->userdata('uf_uniforms');

      if ($this->codes->role_stock_read($uf_stocks) || $this->codes->role_stock_update($uf_stocks)) {
        $data['items_start'] = $items_start;
        $data['itemcode'] = $itemcode;
        $data['styleid'] = $styleid;
        $data['itemname'] = $this->items->select($itemcode);
        $data['codes'] = $this->codes->select_code_stock();
        $data['codes_options'] = 'id="codes" size="1"';

        $this->template->write_view('header', 'common/admin_header');
        $this->template->write('message', $message);
        $this->template->write('title', $this->title.' : '.$data['itemname'].' ('.$itemcode.')');
        $this->template->write('trip', $this->trip.'&nbsp;<img src="'.WEB_CONTEXT.'/images/icons/icon_arrow_right.gif" />&nbsp;'.$data['itemname'].' ('.$itemcode.')');

        if ($data['rows'] = $this->stocks->select_styles_subtotal($itemcode)) {

          $table_settings=array(
                        'table_open' => '<table class="zebraTable" width="1000">',
                        'heading_row_start' => '<tr class="rowEven">',
                        'row_start' => '<tr class="rowOdd">',
                        'row_alt_start' => '<tr class="rowEven">'
          );
          $this->table->set_template($table_settings);

          $this->table->set_heading('Stock #', 'Colour', 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'Subtotal', '');

          foreach($data['rows']->result() as $row)
          {
            $inventory_col = sprintf('<a href="%s/uniformstocks/inventory/%d/%s/%d" title="Inventory - List"><img src="%s/images/buttons/button_inventory.jpg" /></a>', 
            WEB_CONTEXT, $items_start, (string)$row->itemcode, (string)$row->styleid, WEB_CONTEXT);
            $this->table->add_row($row->styleid,$row->colorname,$row->xs,$row->s,$row->m,$row->l,$row->xl,$row->xxl,$row->subtotal,$inventory_col);
          }
          if ($total = $this->stocks->select_items_total($itemcode)) {
            $this->table->add_row('<b>Item Total</b>','',$total->xs,$total->s,$row->m,$total->l,$total->xl,$total->xxl,$total->total,'');
          }

          $data['stocks_table'] = $this->table->generate();

        }

        $this->template->write_view('content', 'stocks', $data);

        // Write to $content
        $this->template->write_view('footer', 'common/footer');
        // Render the template
        $this->template->render();
      }
      else {
        redirect('/uniforms/'.$items_start.'/0/Unauthorised', 'refresh');
      }
    } else {
      redirect('/accounts/index/', 'refresh');
    }
  }

  public function inventory($items_start = 0, $itemcode = '', $styleid = 0, $message = '')
  {
    if ($this->session->userdata('uf_logged_in')) {
      $uf_orders = $this->session->userdata('uf_orders');
      $uf_stocks = $this->session->userdata('uf_stocks');
      $uf_uniforms = $this->session->userdata('uf_uniforms');

      if ($this->codes->role_stock_read($uf_stocks) || $this->codes->role_stock_update($uf_stocks)) {
        $data['items_start'] = $items_start;
        $data['itemcode'] = $itemcode;
        $data['styleid'] = $styleid;
        $data['itemname'] = $this->items->select($itemcode);
        $data['codes'] = $this->codes->select_code_stock();
        $data['codes_options'] = 'id="codes" size="1"';

        if ($styleid !== 0) {
          $style = $this->styles->select_style_by_id($styleid);
        }

        $this->template->write_view('header', 'common/admin_header');
        $this->template->write('message', $message);
        $this->template->write('title', $this->title.' : '.$data['itemname'].' ('.$itemcode.')');
        $this->trip = $this->trip.'&nbsp;<img src="'.WEB_CONTEXT.'/images/icons/icon_arrow_right.gif" />&nbsp;Inventory';
        $this->template->write('trip', $this->trip.'&nbsp;<img src="'.WEB_CONTEXT.'/images/icons/icon_arrow_right.gif" />&nbsp;'.$data['itemname'].' ('.$itemcode.')');

        $table_settings=array(
                                'table_open' => '<table class="zebraTable" width="1000">',
                                'heading_row_start' => '<tr class="rowEven">',
                                'row_start' => '<tr class="rowOdd">',
                                'row_alt_start' => '<tr class="rowEven">'
        );
        $this->table->set_template($table_settings);

        $this->table->set_heading('Date', 'Colour', 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'Subtotal', 'Memo');

        if ($data['rows'] = $this->stocks->select_stocks_by_style($styleid)) {
          foreach($data['rows']->result() as $row)
          {
            $this->table->add_row(substr($row->stockdate,0,10),$row->colorname,$row->xs,$row->s,$row->m,$row->l,$row->xl,$row->xxl,$row->subtotal,$row->code.':'.$row->description.':'.$row->comments);
          }
        }
        $add_col = sprintf('<img src="%s/images/icons/icon_save.gif" /><input type="submit" value="Add New Inventory" title="Add New Inventory"/>', WEB_CONTEXT);
        $xs_col = sprintf('<input type="text" name="xsmall" size="5" />');
        $s_col = sprintf('<input type="text" name="small" size="5" />');
        $m_col = sprintf('<input type="text" name="medium" size="5" />');
        $l_col = sprintf('<input type="text" name="large" size="5" />');
        $xl_col = sprintf('<input type="text" name="xlarge" size="5" />');
        $xxl_col = sprintf('<input type="text" name="xxlarge" size="5" />');
        $memo_col = sprintf('<input type="text" name="comments" size="20" />');

        $this->table->add_row($add_col,$style->colorname,$xs_col,$s_col,$m_col,$l_col,$xl_col,$xxl_col,'',$memo_col);

        if ($total = $this->stocks->select_styles_total($itemcode, $styleid)) {
          $this->table->add_row('<b>Style Total</b>','',$total->xs,$total->s,$total->m,$total->l,$total->xl,$total->xxl,$total->total,'');
        }

        $data['stocks_table'] = $this->table->generate();

        $this->template->write_view('content', 'stock_inventory', $data);

        // Write to $content
        $this->template->write_view('footer', 'common/footer');
        // Render the template
        $this->template->render();
      }
      else {
        redirect('/uniforms/'.$items_start.'/0/Unauthorised', 'refresh');
      }
    } else {
      redirect('/accounts/index/', 'refresh');
    }
  }

  public function insert($items_start = 0, $itemcode = '', $styleid = 0, $message = '')
  {
    $this->form_validation->set_error_delimiters('<div class="messageStackError">', '</div>');
    $this->form_validation->set_rules('xsmall', 'XSmall', 'trim|numeric');
    $this->form_validation->set_rules('small', 'Small', 'trim|numeric');
    $this->form_validation->set_rules('medium', 'Medium', 'trim|numeric');
    $this->form_validation->set_rules('large', 'Large', 'trim|numeric');
    $this->form_validation->set_rules('xlarge', 'XLarge', 'trim|numeric');
    $this->form_validation->set_rules('xxlarge', 'XXLarge', 'trim|numeric');
    $this->form_validation->set_rules('comments', 'Comments', 'trim');

    if ($this->session->userdata('uf_logged_in')) {
      $uf_orders = $this->session->userdata('uf_orders');
      $uf_stocks = $this->session->userdata('uf_stocks');
      $uf_uniforms = $this->session->userdata('uf_uniforms');

      if ($this->codes->role_stock_update($uf_stocks)) {
        if ($this->form_validation->run() == TRUE)
        {
          $xsmall = $this->input->get_post('xsmall',TRUE) == ''? 0 : $this->input->get_post('xsmall',TRUE);
          $small = $this->input->get_post('small',TRUE) == ''? 0 : $this->input->get_post('small',TRUE);
          $medium = $this->input->get_post('medium',TRUE) == ''? 0 : $this->input->get_post('medium',TRUE);
          $large = $this->input->get_post('large',TRUE) == ''? 0 : $this->input->get_post('large',TRUE);
          $xlarge = $this->input->get_post('xlarge',TRUE) == ''? 0 : $this->input->get_post('xlarge',TRUE);
          $xxlarge = $this->input->get_post('xxlarge',TRUE) == ''? 0 : $this->input->get_post('xxlarge',TRUE);
          $comments = $this->input->get_post('comments',TRUE);
          $code = "Inventory";

          if ($this->stocks->insert_stock($itemcode, $styleid, $code, $xsmall, $small, $medium, $large, $xlarge, $xxlarge, $comments)) {
            $this->inventory($items_start,$itemcode,$styleid,INFO_SUCCESS);
          } else {
            $this->inventory($items_start,$itemcode,$styleid,INFO_UNCHANGED);
          }
        } else {
          $this->inventory($items_start,$itemcode,$styleid,ERROR_FORM_VALIDATION);
        }
      } else {
        $this->select($items_start,$itemcode,$styleid,INFO_UNAUTHORISED);
      }
    } else {
      redirect('/accounts/index/', 'refresh');
    }

  }

  public function comments()
  {
    echo 'Uniform Stocks - maintenance.';
  }

}

?>
