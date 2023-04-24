<?php
/**
 * Plugin Name: Influenza r
 * Plugin URI: http://www.influenzar.com
 * Description: Funções Adicionais
 * Version: 1.7
 * Author: Joe
 * Author URI: http://www.influenzar.com
 */

/*
 * GitHub Plugin URI: https://github.com/influenzargit/influenzar_plugin
 * GitHub Branch: main
 */


// Painel Admin
add_action('admin_menu', 'influenza_plugin_instructions');

function influenza_plugin_instructions() {
    add_menu_page(
        'Instruções do Influenza Plugin',
        'Influenza r',
        'manage_options',
        'influenza_plugin_instructions',
        'influenza_plugin_instructions_callback',
        'dashicons-info',
        100
    );
}



function influenza_plugin_instructions_callback() {
    ?>
    <div class="wrap">
        <h1>Influenza r</h1>
		 <p><b>Instruções</b></p>
		 <p>Lista de atalhos:</p>
		[titulo] - Adiciona o nome da página<br>
		[category] - Adiciona o nome da categoria<br>
		[ano] - Adiciona o ano atual<br>
		[project_category] - Adiciona o nome do da categora de projectos<br>
		
		
		 <br> <br>
        <form method="post" action="options.php">
            <?php
            settings_fields('influenza_plugin_instructions');
            do_settings_sections('influenza_plugin_instructions');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}










// Adicionar nome da página
function page_title_sc() {
    if (get_option('ir_page_title')) {
        return get_the_title();
    }
}
add_shortcode('titulo', 'page_title_sc');



// Adicionar nome da categoria linkado
function category_name_shortcode() {
    if (get_option('ir_category_name')) {
        return get_the_category_list(', ');
    }
}
add_shortcode('category', 'category_name_shortcode');



// Mostrar o ano atual
function year_shortcode() {
    if (get_option('ir_year')) {
        $year = date('Y');
        return $year;
    }
}
add_shortcode('ano', 'year_shortcode');



// Adicionar nome da categoria de projeto linkado
function show_project_category_shortcode() {
    if (get_option('ir_project_category')) {
        global $post;
        $terms = get_the_terms($post->ID, 'project_category');
        $output = '';

        if ($terms) {
            foreach ($terms as $term) {
                $output .= '<a href="' . get_term_link($term->term_id) . '">' . $term->name . '</a>';
            }
        }

        return $output;
    }
}
add_shortcode('project_category', 'show_project_category_shortcode');



// Alterar nome da categoria Projects
add_filter('register_taxonomy_args', 'change_taxonomy_category_project', 10, 2);

function change_taxonomy_category_project($args, $taxonomy) {
    if ('project_category' === $taxonomy) {
        $args['rewrite']['slug'] = 'recruitment';
    }
    return $args;
}

function change_taxonomy_project() {
    register_post_type('project', array(
        'labels' => array(
            'name' => __('Recruitment', 'divi'),
            'singular_name' => __('Recruitment', 'divi'),
        ),
        'has_archive' => true,
        'hierarchical' => true,
        'public' => true,
        'rewrite' => array('slug' => 'recruitment', 'with_front' => false),
        'supports' => array(),
    ));
}

add_action('init', 'change_taxonomy_project');



// Alterar o logo da página de login
function custom_login_logo() {
    $logo_url = 'https://www.influenzar.com/wp-content/uploads/2017/07/VICTOR02.png';
    ?>
    <style type="text/css">
        #login h1 a {
            background-image: url(<?php echo $logo_url; ?>);
            background-size: contain;
            width: 100%;
            height: 100px;
        }
    </style>
    <?php
}
add_action('login_enqueue_scripts', 'custom_login_logo');

// Alterar a URL do logo da página de login
function custom_login_logo_url() {
    return 'https://www.influenzar.com'; // Insira a URL personalizada aqui
}
add_filter('login_headerurl', 'custom_login_logo_url');






// MODO DE MANUTENÇÃO
// 1. Adicione uma nova opção no banco de dados para armazenar o status do site (aberto ou fechado)
register_activation_hook(__FILE__, 'influenza_plugin_activation');
function influenza_plugin_activation() {
    add_option('ir_site_status', 'open');
}
// 2. Crie uma função para adicionar uma seção e campo no painel de administração do plugin
function influenza_plugin_settings() {
    add_settings_section('influenza_plugin_section', 'Maintenance mode', null, 'influenza_plugin_instructions');

    add_settings_field('ir_site_status', 'Status do Site', 'influenza_plugin_site_status_callback', 'influenza_plugin_instructions', 'influenza_plugin_section');
    register_setting('influenza_plugin_instructions', 'ir_site_status');
}

add_action('admin_init', 'influenza_plugin_settings');

function influenza_plugin_site_status_callback() {
    $site_status = get_option('ir_site_status');
    ?>
    <select name="ir_site_status">
        <option value="open" <?php selected($site_status, 'open'); ?>>Aberto</option>
        <option value="closed" <?php selected($site_status, 'closed'); ?>>Fechado</option>
    </select>
    <?php
}

// 4. Verifique o status do site e restrinja o acesso a usuários registrados
function restrict_access_to_registered_users() {
    if (get_option('ir_site_status') === 'closed' && !is_user_logged_in()) {
        auth_redirect();
    }
}

add_action('template_redirect', 'restrict_access_to_registered_users');











