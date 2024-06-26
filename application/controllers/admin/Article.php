<?php
defined('BASEPATH') OR exit ('No direct script access allowed');
class article extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $admin = $this->session->userdata('admin');
        if(empty($admin)){
            $this->session->set_flashdata("msg","Please Login!");
            redirect(base_url().'admin/login/index');
        }

    }

    // this method will show articles listing page
    public function index(){
        $this->load->model('Article_model');
        $articles = $this->Article_model->getarticles();
        $data['articles']=$articles;
        $this->load->view("admin/article/list",$data);
    }

    // This method is used to create new article
    public function create(){

        $this->load->helper('common_helper');
        $this->load->model('Article_model');
        $this->load->model('Category_model');

        $categories = $this->Category_model->getCategories();
        $data['categories']=$categories;


        // file upload settings
        $config['upload_path']          = './public/uploads/article/';
        $config['allowed_types']        = 'gif|jpg|png';
        $config['encrypt_name']          = TRUE;
        $this->load->library('upload', $config);
        
        
        $this->form_validation->set_error_delimiters('<p class="invalid_feedback">','</p>');
        $this->form_validation->set_rules('category_id','Category','trim|required');
        $this->form_validation->set_rules('author','Author','trim|required');
        $this->form_validation->set_rules('title','Title','trim|required|min_length[5]');

        if($this->form_validation->run() == true){
            // form validated successfully and we can proceed
            if(!empty($_FILES['image']['name'])){
                //now user has selected a file we will proceed
                if($this->upload->do_upload('image')){
                    //image  uploaded successfully, now we will store it in the database
                    $data = $this->upload->data();

                    //resizing part ... resizeImage method create in common helper file
                    resizeImage($config['upload_path'].$data['file_name'],$config['upload_path'].'thumb_front/'.$data['file_name'],1120,800);
                    resizeImage($config['upload_path'].$data['file_name'],$config['upload_path'].'thumb_admin/'.$data['file_name'],300,250);

                    $formArray['image'] = $data['file_name'] ;
                    $formArray['title'] = $this->input->post('title');
                    $formArray['category'] = $this->input->post('category_id');
                    $formArray['description'] = $this->input->post('description');
                    $formArray['author'] = $this->input->post('author');
                    $formArray['status'] = $this->input->post('status');
                    if(date_default_timezone_set("Asia/Karachi")){
                        $date = date("Y-m-d H:i:s");  
                        $formArray['created_at']= $date;
                    }
                    $this->Article_model->create($formArray);
                    // this code for sweetalert showing code start
                    $_SESSION['status'] = "Article has been added.";
                    $_SESSION['status_code'] = "success";
                    // this code for sweetalert showing code end
        
                    redirect(base_url().'admin/article/index');
                    
                }else{
                    // there was an error uploading the image.
                    $error = $this->upload->display_errors();
                    $data['errorImageUpload'] = $error;
                    $this->load->view('admin/article/create',$data);
                    
                }
            }else{
                //we will create article  without image
                    $formArray['title'] = $this->input->post('title');
                    $formArray['category'] = $this->input->post('category_id');
                    $formArray['description'] = $this->input->post('description');
                    $formArray['author'] = $this->input->post('author');
                    $formArray['status'] = $this->input->post('status');
                    if(date_default_timezone_set("Asia/Karachi")){
                        $date = date("Y-m-d H:i:s");  
                        $formArray['created_at']= $date;
                    }
                    $this->Article_model->create($formArray);
                    // this code for sweetalert showing code start
                    $_SESSION['status'] = "Article has been added.";
                    $_SESSION['status_code'] = "success";
                    // this code for sweetalert showing code end
        
                    redirect(base_url().'admin/article/index');
            }
        }else{
            // form  not validated, you can show errors
            $this->load->view("admin/article/create",$data);
        }

    }
        //this method will show  data of a single record in edit form
    public function edit($id){
        $this->load->model('Article_model');
        $this->load->model('Category_model');

        $categories = $this->Category_model->getCategories();
        $data['categories']=$categories;

        $res = $this->Article_model->getarticle($id);
        if(empty($res)){
            $_SESSION['status'] = "Article not found!";
            $_SESSION['status_code'] = "warning";
            redirect(base_url().'admin/article/index');
        }
        $this->load->helper('common_helper');

        $config['upload_path']          = './public/uploads/article/';
        $config['allowed_types']        = 'gif|jpg|png';
        $config['encrypt_name']          = TRUE;

        $this->load->library('upload', $config);

        $this->form_validation->set_error_delimiters('<p class="invalid_feedback">','</p>');
        $this->form_validation->set_rules('category_id','Category','trim|required');
        $this->form_validation->set_rules('author','Author','trim|required');
        $this->form_validation->set_rules('title','Title','trim|required|min_length[5]');
        if($this->form_validation->run() == true){  

            if(!empty($_FILES['image']['name'])){
                //now user has selected a file we will proceed
                if($this->upload->do_upload('image')){
                    //image  uploaded successfully, now we will store it in the database
                    $data = $this->upload->data();

                    //resizing part ... resizeImage method create in common helper file
                    resizeImage($config['upload_path'].$data['file_name'],$config['upload_path'].'thumb_front/'.$data['file_name'],1120,800);
                    resizeImage($config['upload_path'].$data['file_name'],$config['upload_path'].'thumb_admin/'.$data['file_name'],300,250);

                    $formArray['image'] = $data['file_name'] ;
                    $formArray['title'] = $this->input->post('title');
                    $formArray['category'] = $this->input->post('category_id');
                    $formArray['description'] = $this->input->post('description');
                    $formArray['author'] = $this->input->post('author');
                    $formArray['status'] = $this->input->post('status');
                    if(date_default_timezone_set("Asia/Karachi")){
                        $date = date("Y-m-d H:i:s");  
                        $formArray['updated_at']= $date;
                    }
                    $this->Article_model->update($id,$formArray);
                    
                    if(file_exists('./public/uploads/article/' . $res['image'])) {
                        unlink('./public/uploads/article/' . $res['image']);
                        unlink('public/uploads/article/thumb_admin/' . $res['image']);
                        unlink('public/uploads/article/thumb_front/' . $res['image']);
                    }
                    

                    // this code for sweetalert showing code start
                    $_SESSION['status'] = "Article updated Successfully.";
                    $_SESSION['status_code'] = "success";
                    // this code for sweetalert showing code end
        
                    redirect(base_url().'admin/article/index');
                    
                }else{
                    // there was an error uploading the image.
                    $error = $this->upload->display_errors();
                    $data['errorImageUpload'] = $error;
                    $data['res'] = $res;
                    $this->load->view('admin/article/edit',$data);    
                }
            }else{
                //we will create category  without image
                    $formArray['image'] = $data['file_name'] ;
                    $formArray['title'] = $this->input->post('title');
                    $formArray['category'] = $this->input->post('category_id');
                    $formArray['description'] = $this->input->post('description');
                    $formArray['author'] = $this->input->post('author');
                    $formArray['status'] = $this->input->post('status');
                    if(date_default_timezone_set("Asia/Karachi")){
                        $date = date("Y-m-d H:i:s");  
                        $formArray['updated_at']= $date;
                    }
                $this->Article_model->update($id,$formArray);
                // this code for sweetalert showing code start
                $_SESSION['status'] = "Article updated Successfully.";
                $_SESSION['status_code'] = "success";
                // this code for sweetalert showing code end
    
                redirect(base_url().'admin/article/index');
            }
    }else{
        $data['res'] = $res;
        $this->load->view('admin/article/edit',$data);
    }
    
 }

 public function delete($id){
    $this->load->model('Article_model');
    $res = $this->Article_model->getArticle($id);
    if(empty($res)){
        $_SESSION['status'] = "Article not found!";
        $_SESSION['status_code'] = "warning";
        redirect(base_url().'admin/category/index');
    }
    if(file_exists('./public/uploads/article/' . $res['image'])) {
        unlink('./public/uploads/article/' . $res['image']);
        unlink('public/uploads/article/thumb_admin/' . $res['image']);
        unlink('public/uploads/article/thumb_front/' . $res['image']);
    }

    $this->Article_model->delete($id);
    // this code for sweetalert showing code start
    $_SESSION['status'] = "Article Deleted Successfully.";
    $_SESSION['status_code'] = "success";
    // this code for sweetalert showing code end
    redirect(base_url().'admin/article/index');
}
}
?>  