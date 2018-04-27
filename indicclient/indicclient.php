<?php

/* 
Plugin Name: Indicação de Clientes
Plugin URI: https://github.com/DiegoCacau/
Description: Cria uma área para indicação de clientes e um painel administrativo.
Version: 0.5
Author: Diego Cacau
Author URI: diegocacau.com
License: GPLv2 or later
*/

register_activation_hook( __FILE__, 'create_indicclient_indicados' );
register_activation_hook( __FILE__, 'create_indicclient_emails' );

function create_indicclient_indicados()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'indicclient_indicados';

    $sql = "CREATE TABLE $table_name (
      id int(10) NOT NULL AUTO_INCREMENT,
      nome varchar(70) DEFAULT '' NOT NULL,
      email varchar(70) DEFAULT '' NOT NULL,
      telefone varchar(15) DEFAULT '' NOT NULL,
      horario varchar(15) DEFAULT '' NOT NULL,
      data varchar(12) DEFAULT '' NOT NULL,
      indicador varchar(70) DEFAULT '' NOT NULL,
      estatus varchar(25) DEFAULT '' NOT NULL,
      UNIQUE KEY id (id)
    );";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

function create_indicclient_emails()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'indicclient_emails';

    $sql = "CREATE TABLE $table_name (
      id int(10) NOT NULL AUTO_INCREMENT,
      email varchar(70) DEFAULT '' NOT NULL,
      assunto varchar(120) DEFAULT '' NOT NULL,
      corpo varchar(3000) DEFAULT '' NOT NULL,
      tipo varchar(25) DEFAULT '' NOT NULL,
      UNIQUE KEY id (id)
    );";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    $email = " ";
    $assunto = " ";
    $corpo =  " ";
    $tipo  = array(0 => 'Pendente', 1 => 'Primeiro Contato', 2 => 'Visita Agendada', 3 => 'Contrato Assinado');

    $num =  $wpdb->get_var( ('SELECT COUNT(*) FROM '.$table_name) );
    
    if(!$num){
    	for ($i = 0; $i <= 3; $i++)  {
    	
		    $wpdb->insert( 
		        $table_name, 
		        array(

		            'email' => $email,
		            'assunto' => $assunto,
		            'corpo'  => $corpo,
		            'tipo' => $tipo[$i]
		        ) ,

		        array(
		                '%s', 
		                '%s',
		                '%s',
		                '%s'
		                
		        )
		    );

		}
    }	    
}

function salvar_dados($nome, $email, $telefone, $horario, $data, $indicador){
	global $wpdb;
    $table_name = $wpdb->prefix . 'indicclient_indicados';
    $estatus = 'Pendente';

    $wpdb->insert( 
        $table_name, 
        array( 
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'horario'  => $horario,
            'data' => $data,
            'indicador' => $indicador, 
            'estatus' => $estatus
        ) ,

        array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s', 
                '%s',
                '%s'
                
        )
    );

}

function add_admin_script(){
    wp_register_style( 'my-plugin_css', plugins_url( '/indicclient/admin_css.css' ) );
    wp_enqueue_style( 'my-plugin_css' );

    wp_register_script( 'my-plugin_js', plugins_url( '/indicclient/admin_js.js' ),array('jquery') );
    wp_enqueue_script( 'my-plugin_js' );

}

function add_admin_script_sub(){
    wp_register_style( 'my-plugin_css_sub', plugins_url( '/indicclient/admin_css_sub.css' ) );
    wp_enqueue_style( 'my-plugin_css_sub' );

    wp_register_script( 'my-plugin_js_sub', plugins_url( '/indicclient/admin_js_sub.js' ),array('jquery') );
    wp_enqueue_script( 'my-plugin_js_sub' );

}

function add_user_script(){
    wp_register_style( 'my-plugin_css_user', plugins_url( '/indicclient/user_css.css' ) );
    wp_enqueue_style( 'my-plugin_css_user' );

    wp_register_script( 'my-plugin_js_user', plugins_url( '/indicclient/user_js.js' ),array('jquery') );
    wp_enqueue_script( 'my-plugin_js_user' );
    

}

function indicclient_add_bootstrap(){
	// // JS
    wp_register_script('indicclient_prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
    wp_enqueue_script('indicclient_prefix_bootstrap');

    // // CSS
    wp_register_style('indicclient_bootstrap1', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
    wp_enqueue_style('indicclient_bootstrap1');
}

add_action('admin_menu', 'test_plugin_setup_menu_indicclient');


function test_plugin_setup_menu_indicclient(){
    add_menu_page( 'Indicação de Clientes', 'Indic. Cliente', 'manage_options', 'indicclient', 'indicclient_init' );
	add_submenu_page('indicclient','Configuração de Emails', 'Config. Email', 'manage_options', 'indicclient_email' ,'indicclient_init_sub' );   
}


function indicclient_init(){
	indicclient_add_bootstrap();

	global $wpdb;

	if(isset($_POST['change_status'])){
		$id_to_change = $_POST['change_status'];
		$estatus_tipo = $_POST['estatus_tipo'];
		if($estatus_tipo == 'pendente'){
			$estatus_change = 'Pendente';
		}
		else if($estatus_tipo == 'primeiro'){
			$estatus_change = 'Primeiro Contato';
		}
		else if($estatus_tipo == 'visita'){
			$estatus_change = 'Visita Agendada';
		}
		else if($estatus_tipo == 'contrato'){
			$estatus_change = 'Contrato Assinado';
		}
		

		$table_indc = $wpdb->prefix . 'indicclient_indicados';

		$search_todos = $wpdb->query( ("UPDATE ".$table_indc." SET estatus = '".$estatus_change."' WHERE id = '".$id_to_change."'"));

		$table_msg = $wpdb->prefix . 'indicclient_emails';
		$search_msg = $wpdb->get_results( ("SELECT * FROM  $table_msg WHERE tipo ='".$estatus_change."'"));
		$headers="";
		foreach($search_msg as $search_msg){
			$subject = $search_msg->assunto;
			$txt = $search_msg->corpo;
			$headers = "From: ".$search_msg->email;
		}


		$user_mail = $wpdb->get_results( ("SELECT * FROM  $table_indc WHERE id = '".$id_to_change."'"));
		foreach($user_mail as $user_mail){
			$user = get_user_by( "login", $user_mail->indicador );
			$to = $user->user_email;
		}

		
		

		
		if($headers != ""){

			mail($to,$subject,$txt,$headers);
		}
		
	} 

	echo'<div id="indc_main">'; 
		echo '<div id="div_esq_indic">';	
			echo '<table class="table-striped">';
				echo '<thead>';
					echo '<tr>';
						echo '<th scope="col" class="td_indic_1">Usuário</td>';
						echo '<th scope="col" class="td_indic_2">Indicados Pendentes</td>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';

				$table = $wpdb->prefix . 'users';
				$search = $wpdb->get_results( ("SELECT * FROM  $table"));
				foreach ($search as $search) {
					$table_ind = $wpdb->prefix . 'indicclient_indicados';
					$search_ind = $wpdb->get_results( ("SELECT * FROM  $table_ind WHERE indicador = '".$search->user_login."' AND NOT estatus = 'Contrato Assinado'"));
				
					echo '<tr class="tr_indic" id="'.$search->user_login.'">';
						echo '<td class="td_indic_1">';
							echo $search->user_login;
						echo '</td>';
						echo '<td class="td_indic_2">';
							if($search_ind) {
								echo COUNT($search_ind);
							}
							else{
								echo '0';
							}
						echo '</td>';
					echo '</tr>';
				}					
				echo '</tbody>';
			echo '</table>';
		echo '</div>';

		echo '<div id="div_dir_indic">';
			echo '<div id="div_tabela_ind">';
			echo '<table class="table-striped">';
				echo '<thead>';
					echo '<tr>';
						echo '<th scope="col" class="td_indic_1">Nome</td>';
						echo '<th scope="col" class="td_indic_2">Telefone</td>';
						echo '<th scope="col" class="td_indic_2">Email</td>';
						echo '<th scope="col" class="td_indic_2">Situação</td>';
						echo '<th scope="col" class="td_indic_2">Data de Indicação</td>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';

				$table_indc = $wpdb->prefix . 'indicclient_indicados';
				$search_todos = $wpdb->get_results( ("SELECT * FROM  $table_indc"));
				foreach ($search_todos as $search_todos) {					
					echo '<tr  class="'.$search_todos->indicador.' indic_none tr_clicavel" value="'.$search_todos->id.'">';
						echo '<td class="td_indic_1">';
							echo $search_todos->nome;
						echo '</td>';
						echo '<td class="td_indic_2">';
							echo $search_todos->telefone;
						echo '</td>';
						echo '<td class="td_indic_2">';
							echo $search_todos->email;
						echo '</td>';
						if($search_todos->estatus == 'Contrato Assinado'){
							echo '<td class="td_indic_2 td_assinado">';
						}
						else if($search_todos->estatus == 'Visita Agendada'){
							echo '<td class="td_indic_2 td_agendado">';
						}
						else{
							echo '<td class="td_indic_2 td_pendente">';
						}
							echo $search_todos->estatus;
						echo '</td>';
						echo '<td class="td_indic_2">';
							echo $search_todos->data;
						echo '</td>';
					echo '</tr>';

				}
				echo '</tbody>';
			echo '</table>';
			echo '</div>';	
			echo '<div id="indicclient_status" style = "width:100%; text-align:center;">';
				echo '<form  method="post" enctype="multipart/form-data">';
					echo '<button  style="display:none;" id="change_status" name ="change_status" value="">Mudar Estatus</button>';
					echo '<div style = "width:100%; text-align:left;">';
						echo '<input type="radio" name="estatus_tipo"  style="float:left;" value="pendente" required>Pendente <br>';
						echo '<input type="radio" name="estatus_tipo"  style="float:left;" value="primeiro">Primeiro Contato <br>';
						echo '<input type="radio" name="estatus_tipo"  style="float:left;" value="visita">Visita Agendada <br>';
						echo '<input type="radio" name="estatus_tipo"  style="float:left;" value="contrato">Contrato Assinado <br>';	
					echo '</div>';
				echo '</form>';
				echo '<button id="change_status_2">Mudar Estatus</button>';		
			echo '</div>';	
		echo '</div>';
	echo'</div>';

	add_admin_script();
}


add_shortcode("indic_client", "indic_client_shortcode");


function indic_client_shortcode(){
	indicclient_add_bootstrap();

	if(isset($_POST['clien_nome'])) $nome = sanitize_text_field($_POST['clien_nome']);
	if(isset($_POST['clien_email'])) $email = sanitize_email($_POST['clien_email']);
	if(isset($_POST['clien_fone'])) $fone = $_POST['clien_fone'];
	if(isset($_POST['clien_hora'])) $hora = sanitize_text_field($_POST['clien_hora']);
	if(isset($_POST['enviar_indic_not']) and isset($nome) and isset($email) and isset($fone)){
		
		$data = strftime("%d-%m-%Y",$date);
		save_dados($nome, $email, $fone, $hora, $data);
		
	};

	echo '<div id= "cliente_visu">';
		echo '<div id="part_button">';
			echo '<button class="btn" id="indicar_amigo">Indicar Amigo</button>';
			echo '<button class="btn" id="amigo_indicado">Amigos Indicados</button>';
		echo "</div>";
		echo '<div id="form_client">';
			echo '<form  method="post" enctype="multipart/form-data">';
				
				echo '<div class="row">';
					
					echo '<div class="form-group">';
						echo '<label class="" for="clien_nome">Nome</label>';
						echo '<input type="text" id="clien_nome" name="clien_nome" class="form-control" required><br>';
					echo '</div>';	

					echo '<div class="form-group">';	
						echo '<label class="label_cliente_visu" for="clien_email">E-mail</label>';
						echo '<input type="text" id="clien_email" name="clien_email" required> <br>';
					echo '</div>';		

					echo '<div class="form-group">';	
						echo '<label class="label_cliente_visu" for="clien_fone">Telefone</label>';
						echo '<input type="text" id="clien_fone" name="clien_fone" required> <br>';
					echo '</div>';		

					echo '<div class="form-group">';	
						echo '<label id="label_select_turno" for="select_turno">Melhor turno para contato: </label>';
						echo '<select id="select_turno" name="clien_hora" required>';
						  echo '<option value="" selected></option>';
						  echo '<option value="Manha">Manhã</option>';
						  echo '<option value="Tarde">Tarde</option>';
						  echo '<option value="Noite">Noite</option>';
						echo '</select>';
						echo '<button id="enviar_indic_not" name="enviar_indic_not" style="display:none;">Enviar</button>';
					echo '</div>';	
						
				echo '</div>';
			echo '</form>';

			echo '<button id="enviar_indic">Enviar</button>';
		echo '</div>';

		echo '<div id="indcados_client">';
			$current_user = wp_get_current_user();
			$indicador = $current_user->user_login;
			global $wpdb;
			$table_ind = $wpdb->prefix . 'indicclient_indicados';
			$search_ind = $wpdb->get_results( ("SELECT * FROM  $table_ind WHERE indicador = '".$indicador."'"));

			echo '<table>';
				echo '<thead>';
					echo '<tr>';
						echo '<td>Indicado</td>';
						echo '<td>Estatus</td>';
						echo '<td>Data de Indicação</td>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
				foreach ($search_ind as $search_ind) {
					echo '<tr>';
						echo '<td>';
							echo $search_ind->nome;
						echo '</td>';
						echo '<td>';
							echo $search_ind->estatus;
						echo '</td>';
						echo '<td>';
							echo $search_ind->data;
						echo '</td>';
					echo '</tr>';
					
				}
					
				echo '</tbody>';
			echo '</table>';
		echo '</div>';
	echo "</div>";

	add_user_script();
}

function save_dados($nome, $email, $telefone, $horario, $data){

	$current_user = wp_get_current_user();
	$indicador = $current_user->user_login;
	global $wpdb;
	$table = $wpdb->prefix . 'indicclient_indicados';
	$search = $wpdb->get_results( ("SELECT * FROM  $table WHERE email = '".$email."'"));
	$sim_ou_nao = true;

	if($search){
		foreach ($search as $search) {
			if(($search->nome == $nome) and (($search->email == $email)) and ($search->telefone == $telefone)){
				$sim_ou_nao = false;
			}
		}
	}

	if($sim_ou_nao){
		salvar_dados($nome, $email, $telefone, $horario, $data, $indicador);
	}
}


function indicclient_init_sub(){
	indicclient_add_bootstrap();

	global $wpdb;
	$table_email = $wpdb->prefix . 'indicclient_emails';
	if(isset($_POST["send_button_pendente_email"])){
		$pendente_email_remetente = sanitize_text_field($_POST['pendente_email_remetente']);
		$pendente_email_assunto = sanitize_text_field($_POST['pendente_email_assunto']);
		$pendente_email_corpo = ($_POST['pendente_email_corpo']);


		$search_todos = $wpdb->query( ("UPDATE ".$table_email." SET email = '".$pendente_email_remetente."' WHERE tipo = 'Pendente'"));
		$search_todos = $wpdb->query( ("UPDATE ".$table_email." SET assunto = '".$pendente_email_assunto."' WHERE tipo = 'Pendente'"));
		$search_todos = $wpdb->query( ("UPDATE ".$table_email." SET corpo = '".$pendente_email_corpo."' WHERE tipo = 'Pendente'"));
	}
	else if(isset($_POST["send_button_primeiro_email"])){
		$primeiro_email_remetente = sanitize_text_field($_POST['primeiro_email_remetente']);
		$primeiro_email_assunto = sanitize_text_field($_POST['primeiro_email_assunto']);
		$primeiro_email_corpo = ($_POST['primeiro_email_corpo']);

		echo $primeiro_email_remetente.'<br>';
		echo $primeiro_email_assunto.'<br>';
		echo $primeiro_email_corpo.'<br>';

		$search_todos = $wpdb->query( ("UPDATE ".$table_email." SET email = '".$primeiro_email_remetente."' WHERE tipo = 'Primeiro Contato'"));
		$search_todos = $wpdb->query( ("UPDATE ".$table_email." SET assunto = '".$primeiro_email_assunto."' WHERE tipo = 'Primeiro Contato'"));
		$search_todos = $wpdb->query( ("UPDATE ".$table_email." SET corpo = '".$primeiro_email_corpo."' WHERE tipo = 'Primeiro Contato'"));
	}
	else if(isset($_POST["send_button_visita_email"])){
		$visita_email_remetente = sanitize_text_field($_POST['visita_email_remetente']);
		$visita_email_assunto = sanitize_text_field($_POST['visita_email_assunto']);
		$visita_email_corpo = ($_POST['visita_email_corpo']);

		echo $visita_email_remetente.'<br>';
		echo $visita_email_assunto.'<br>';
		echo $visita_email_corpo.'<br>';

		$search_todos = $wpdb->query( ("UPDATE ".$table_email." SET email = '".$visita_email_remetente."' WHERE tipo = 'Visita Agendada'"));
		$search_todos = $wpdb->query( ("UPDATE ".$table_email." SET assunto = '".$visita_email_assunto."' WHERE tipo = 'Visita Agendada'"));
		$search_todos = $wpdb->query( ("UPDATE ".$table_email." SET corpo = '".$visita_email_corpo."' WHERE tipo = 'Visita Agendada'"));
	}
	else if(isset($_POST["send_button_assinado_email"])){
		$assinado_email_remetente = sanitize_text_field($_POST['assinado_email_remetente']);
		$assinado_email_assunto = sanitize_text_field($_POST['assinado_email_assunto']);
		$assinado_email_corpo = ($_POST['assinado_email_corpo']);

		echo $assinado_email_remetente.'<br>';
		echo $assinado_email_assunto.'<br>';
		echo $assinado_email_corpo.'<br>';

		$search_todos = $wpdb->query( ("UPDATE ".$table_email." SET email = '".$assinado_email_remetente."' WHERE tipo = 'Contrato Assinado'"));
		$search_todos = $wpdb->query( ("UPDATE ".$table_email." SET assunto = '".$assinado_email_assunto."' WHERE tipo = 'Contrato Assinado'"));
		$search_todos = $wpdb->query( ("UPDATE ".$table_email." SET corpo = '".$assinado_email_corpo."' WHERE tipo = 'Contrato Assinado'"));
	}

	echo '<div id="sub_email">';
		echo '<ul class="tabs">';
            echo '<li class="tab-link current" data-tab="tab-1">Pendente</li>';
            echo '<li class="tab-link" data-tab="tab-2">Primeiro Contato</li>';
            echo '<li class="tab-link" data-tab="tab-3">Visita Agendada</li>';
            echo '<li class="tab-link" data-tab="tab-4">Contrato Assinado</li>';
        echo '</ul>';

        echo '<div id="tab-1" class="tab-content current" >';
        	echo '<h2>Email de Indicação Pendente</h2>';
        	echo '<form  method="post" id="pendente_email" enctype="multipart/form-data">';
	        	echo '<label class="" for="pendente_email_remetente">Seu Email</label>';
	        	
	        	$search_penden = $wpdb->get_results( ("SELECT * FROM  $table_email WHERE tipo = 'Pendente'"));

	        	$pendente_mail = '';
	        	$pendente_ass = '';
	        	$pendente_corp = '';	      

	        	foreach ($search_penden as $search_penden) {

	        		$pendente_mail = $search_penden->email;
	        		$pendente_ass = $search_penden->assunto;
	        		$pendente_corp = $search_penden->corpo;	        			

				}
				echo '<input type="text" id="pendente_email_remetente" name="pendente_email_remetente" value="'.$pendente_mail.'" required><br>';	
				echo '<label class="" for="pendente_email_assunto">Assunto:</label>';
				echo '<input type="text" id="pendente_email_assunto" name="pendente_email_assunto" value="'.$pendente_ass.'" required><br>';
				echo '<button style="display:none;" id="send_button_pendente_email" name="send_button_pendente_email">Enviar</button>';	
			echo '</form>';
			echo '<textarea name="pendente_email_corpo" id="pendente_email_corpo" form="pendente_email" cols="60" rows="30" wrap="soft" maxlength="3000" placeholder="Digite o corpor do email. HTML é permitido.">'.$pendente_corp.'</textarea>';

			echo '<div>';
			echo '<button id="button_pendente_email">Enviar</button>';
			echo '</div>';

        echo '</div>';
        echo '<div id="tab-2" class="tab-content">';
        	echo '<h2>Email de Primeiro Contato</h2>';
        	echo '<form  method="post" id="primeiro_email" enctype="multipart/form-data">';

        		$search_prim = $wpdb->get_results( ("SELECT * FROM  $table_email WHERE tipo = 'Primeiro Contato'"));

	        	$prim_mail = '';
	        	$prim_ass = '';
	        	$prim_corp = '';	      

	        	foreach ($search_prim as $search_prim) {

	        		$prim_mail = $search_prim->email;
	        		$prim_ass = $search_prim->assunto;
	        		$prim_corp = $search_prim->corpo;	        			

				}

	        	echo '<label class="" for="primeiro_email_remetente">Seu Email</label>';
				echo '<input type="text" id="primeiro_email_remetente" name="primeiro_email_remetente" value="'.$prim_mail.'" required><br>';

				echo '<label class="" for="primeiro_email_assunto">Assunto:</label>';
				echo '<input type="text" id="primeiro_email_assunto" name="primeiro_email_assunto" value="'.$prim_ass.'"  required><br>';
				echo '<button style="display:none;" id="send_button_primeiro_email" name="send_button_primeiro_email">Enviar</button>';	
			echo '</form>';
			echo '<textarea name="primeiro_email_corpo" id="primeiro_email_corpo" form="primeiro_email" cols="60" rows="30" wrap="soft" maxlength="3000" placeholder="Digite o corpor do email. HTML é permitido.">'.$prim_corp.'</textarea>';

			echo '<div>';
			echo '<button id="button_primeiro_email">Enviar</button>';
			echo '</div>';

        echo '</div> ';
        echo '<div id="tab-3" class="tab-content" >';

        	echo '<h2>Email de Visita Agendada</h2>';
        	echo '<form  method="post" id="visita_email" enctype="multipart/form-data">';

        		$search_agen = $wpdb->get_results( ("SELECT * FROM  $table_email WHERE tipo = 'Visita Agendada'"));

	        	$agen_mail = '';
	        	$agen_ass = '';
	        	$agen_corp = '';	      

	        	foreach ($search_agen as $search_agen) {

	        		$agen_mail = $search_agen->email;
	        		$agen_ass = $search_agen->assunto;
	        		$agen_corp = $search_agen->corpo;	        			

				}

	        	echo '<label class="" for="visita_email_remetente">Seu Email</label>';
				echo '<input type="text" id="visita_email_remetente" name="visita_email_remetente" value="'.$agen_mail.'" required><br>';

				echo '<label class="" for="visita_email_assunto">Assunto:</label>';
				echo '<input type="text" id="visita_email_assunto" name="visita_email_assunto" value="'.$agen_ass.'" required><br>';
				echo '<button style="display:none;" id="send_button_visita_email" name="send_button_visita_email">Enviar</button>';	
			echo '</form>';
			echo '<textarea name="visita_email_corpo" id="visita_email_corpo" form="visita_email" cols="60" rows="30" wrap="soft" maxlength="3000" placeholder="Digite o corpor do email. HTML é permitido.">'.$agen_corp.'</textarea>';
			echo '<div>';
			echo '<button id="button_visita_email">Enviar</button>';
			echo '</div>';

        echo '</div>';
        echo '<div id="tab-4" class="tab-content" >';
        	
        	echo '<h2>Email de Contrato Assinado</h2>';
        	echo '<form  method="post" id="assinado_email" enctype="multipart/form-data">';

        		$search_assi = $wpdb->get_results( ("SELECT * FROM  $table_email WHERE tipo = 'Contrato Assinado'"));

	        	$assi_mail = '';
	        	$assi_ass = '';
	        	$assi_corp = '';	      

	        	foreach ($search_assi as $search_assi) {

	        		$assi_mail = $search_assi->email;
	        		$assi_ass = $search_assi->assunto;
	        		$assi_corp = $search_assi->corpo;	        			

				}

	        	echo '<label class="" for="assinado_email_remetente">Seu Email</label>';
				echo '<input type="text" id="assinado_email_remetente" name="assinado_email_remetente" value="'.$assi_mail.'" required><br>';

				echo '<label class="" for="assinado_email_assunto">Assunto:</label>';
				echo '<input type="text" id="assinado_email_assunto" name="assinado_email_assunto" value="'.$assi_ass.'" required><br>';
				echo '<button style="display:none;" id="send_button_assinado_email" name="send_button_assinado_email">Enviar</button>';	
			echo '</form>';
			echo '<textarea name="assinado_email_corpo" id="assinado_email_corpo" form="assinado_email" cols="60" rows="30" wrap="soft" maxlength="3000" placeholder="Digite o corpor do email. HTML é permitido.">'.$assi_corp.'</textarea>';

			echo '<div>';
			echo '<button id="button_assinado_email">Enviar</button>';
			echo '</div>';


        echo '</div>';
	echo "</div>";


	add_admin_script_sub();
}