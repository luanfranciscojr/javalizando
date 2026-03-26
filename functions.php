<?php
/**
 * Funções do tema filho Javalizando.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enfileira estilos do GeneratePress e do tema filho.
 */
function javalizando_enqueue_styles()
{
    wp_enqueue_style(
        'generatepress-style',
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme('generatepress')->get('Version')
    );

    wp_enqueue_style(
        'javalizando-child-style',
        get_stylesheet_uri(),
        ['generatepress-style'],
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'javalizando_enqueue_styles');

/**
 * Adiciona seções estratégicas na home usando hook do GeneratePress.
 */
function javalizando_render_home_intro()
{
    if (!is_home() && !is_front_page()) {
        return;
    }

    echo '<section class="jv-home-hero">';
    echo '<span class="jv-home-hero__kicker">Java + Spring Boot</span>';
    echo '<h1 class="jv-home-hero__title">Aprenda Java e Spring Boot com guias práticos para projetos reais.</h1>';
    echo '<p class="jv-home-hero__description">Conteúdo direto ao ponto para desenvolvedores iniciantes e intermediários evoluírem em backend moderno, APIs REST e boas práticas.</p>';
    echo '<div class="jv-home-hero__actions">';
    echo '<a class="jv-button" href="#posts">Ver artigos mais recentes</a>';
    echo '<a class="jv-button jv-button--secondary" href="#comece-aqui">Comece aqui</a>';
    echo '</div>';
    echo '</section>';

    $tracks = [
        ['slug' => 'java', 'fallback' => 'Java'],
        ['slug' => 'spring-boot', 'fallback' => 'Spring Boot'],
        ['slug' => 'api-rest', 'fallback' => 'API REST'],
        ['slug' => 'boas-praticas', 'fallback' => 'Boas Práticas'],
    ];

    echo '<section class="jv-home-tracks" aria-label="Trilhas de conteúdo">';
    echo '<h2 class="jv-section-title">Trilhas para acelerar seu aprendizado</h2>';
    echo '<div class="jv-track-grid">';

    foreach ($tracks as $track) {
        $category = get_category_by_slug($track['slug']);
        $title = $track['fallback'];
        $description = 'Conteúdo técnico organizado para estudar com foco.';
        $link = home_url('/');

        if ($category instanceof WP_Term) {
            $title = $category->name;
            $description = !empty($category->description) ? $category->description : $description;
            $link = get_category_link($category->term_id);
        }

        echo '<article class="jv-track-card jv-card">';
        echo '<h3><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></h3>';
        echo '<p>' . esc_html(wp_trim_words($description, 20)) . '</p>';
        echo '<a href="' . esc_url($link) . '">Explorar trilha</a>';
        echo '</article>';
    }

    echo '</div>';
    echo '</section>';
}
add_action('generate_after_header', 'javalizando_render_home_intro', 15);

/**
 * Newsletter e seção "comece aqui" ao final da listagem principal da home.
 */
function javalizando_render_home_conversion_sections()
{
    if (!is_home() && !is_front_page()) {
        return;
    }

    echo '<section id="comece-aqui" class="jv-card" style="padding:1.25rem;margin-top:2rem;">';
    echo '<h2 class="jv-section-title">Comece aqui</h2>';
    echo '<p>Se você está iniciando agora, recomendamos seguir esta ordem: fundamentos de Java, orientação a objetos, Spring Boot, construção de APIs REST e testes.</p>';
    echo '</section>';

    echo '<section class="jv-newsletter" aria-label="Newsletter">';
    echo '<h3>Receba novos artigos de Java e Spring Boot por e-mail</h3>';
    echo '<p>Uma curadoria rápida com conteúdos práticos para evoluir na carreira backend.</p>';
    echo '<form method="post" action="#">';
    echo '<input type="email" name="newsletter_email" placeholder="Seu melhor e-mail" aria-label="Seu melhor e-mail">';
    echo '<button type="submit">Quero receber</button>';
    echo '</form>';
    echo '</section>';
}
add_action('generate_after_main_content', 'javalizando_render_home_conversion_sections', 15);

/**
 * Remove widgets padrões pouco úteis para foco em retenção.
 */
function javalizando_unregister_default_widgets()
{
    unregister_widget('WP_Widget_Meta');
    unregister_widget('WP_Widget_Archives');
    unregister_widget('WP_Widget_Calendar');
    unregister_widget('WP_Widget_Tag_Cloud');
    unregister_widget('WP_Widget_Recent_Comments');
}
add_action('widgets_init', 'javalizando_unregister_default_widgets', 11);

/**
 * Gera índice simples com base em headings H2/H3.
 */
function javalizando_add_toc_to_content($content)
{
    if (!is_singular('post') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    if (stripos($content, '<h2') === false) {
        return $content;
    }

    preg_match_all('/<h([2-3])([^>]*)>(.*?)<\/h[2-3]>/i', $content, $matches, PREG_SET_ORDER);

    if (empty($matches)) {
        return $content;
    }

    $toc = '<nav class="jv-toc" aria-label="Índice do artigo"><h2>Índice do artigo</h2><ol>';

    foreach ($matches as $index => $heading) {
        $level = isset($heading[1]) ? absint($heading[1]) : 2;
        $attributes = isset($heading[2]) ? $heading[2] : '';
        $title = trim(wp_strip_all_tags($heading[3]));
        $anchor = 'secao-' . ($index + 1);

        if (preg_match('/id=["\']([^"\']+)["\']/i', $attributes, $existing_id)) {
            $anchor = sanitize_title($existing_id[1]);
        } else {
            $replacement = '<h' . $level . $attributes . ' id="' . esc_attr($anchor) . '">' . $heading[3] . '</h' . $level . '>';
            $content = preg_replace('/' . preg_quote($heading[0], '/') . '/', $replacement, $content, 1);
        }

        $toc .= '<li class="jv-toc-level-' . $level . '"><a href="#' . esc_attr($anchor) . '">' . esc_html($title) . '</a></li>';
    }

    $toc .= '</ol></nav>';

    return $toc . $content;
}
add_filter('the_content', 'javalizando_add_toc_to_content', 15);

/**
 * Exibe box do autor e artigos relacionados ao final do conteúdo.
 */
function javalizando_enhance_single_post($content)
{
    if (!is_singular('post') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $author_id = get_the_author_meta('ID');
    $author_name = get_the_author();
    $author_description = get_the_author_meta('description', $author_id);

    $author_box = '<section class="jv-author-box" aria-label="Sobre o autor">';
    $author_box .= '<h3 class="jv-author-box__title">Sobre ' . esc_html($author_name) . '</h3>';
    $author_box .= '<p>' . esc_html($author_description ?: 'Autor do Javalizando, compartilhando conteúdos práticos sobre Java e Spring Boot.') . '</p>';
    $author_box .= '</section>';

    $related_query = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => 3,
        'post__not_in' => [get_the_ID()],
        'ignore_sticky_posts' => true,
        'category__in' => wp_get_post_categories(get_the_ID()),
    ]);

    $related_html = '';

    if ($related_query->have_posts()) {
        $related_html .= '<section class="jv-related" aria-label="Artigos relacionados">';
        $related_html .= '<h2>Artigos relacionados</h2>';
        $related_html .= '<div class="jv-related-posts">';

        while ($related_query->have_posts()) {
            $related_query->the_post();
            $related_html .= '<article class="related-post">';
            $related_html .= '<h3><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
            $related_html .= '<p>' . esc_html(wp_trim_words(get_the_excerpt(), 18)) . '</p>';
            $related_html .= '</article>';
        }

        $related_html .= '</div>';
        $related_html .= '</section>';
        wp_reset_postdata();
    }

    return $content . $author_box . $related_html;
}
add_filter('the_content', 'javalizando_enhance_single_post', 25);
