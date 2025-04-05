<?php
namespace LinkGallery\Controllers\GutenbergBlock;

/**
 * gutenberg 编辑器区块中，增加一个组件，用于向页面中增加“pdf”链接功能
 */
class NormalPdfInsertController extends BaseGutenBergBlockController
{
  protected $blockName = 'link-gallery/pdf-insert';
  protected $blockTitle = 'PDF リンク挿入';

  public function __construct()
  {
      add_action('init', [$this, 'registerBlock']);
  }

  private function localize_block_data() {
    $args = array(
      'post_type' => 'attachment',
      'post_mime_type' => 'application/pdf',
      'posts_per_page' => -1,
      'post_status' => 'inherit'
    );

    $pdfs = get_posts($args);
    $pdf_data = [];

    foreach ($pdfs as $pdf) {
      $item = array(
        'id' => $pdf->ID,
        'filename' => $pdf->post_title,
        'url' => wp_get_attachment_url($pdf->ID)
      );
      $item['filename'] = basename($item['url']);
      $pdf_data[] = $item;
    }
    wp_localize_script(
      'pdf-insert-block-editor',
      'pdfGalleryData',
      [
        'pdfs' => $pdf_data
      ]
    );
  }

  public function registerBlock()
  {
      if (!function_exists('register_block_type')) {
          return;
      }

      wp_register_script(
          'pdf-insert-block-editor',
          plugins_url('resources/views/js/blocks/pdf-insert/index.js', dirname(dirname(dirname(__FILE__)))),
          ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components']
      );

      wp_register_style(
          'pdf-insert-block-style',
          plugins_url('resources/views/js/blocks/pdf-insert/style.css', dirname(dirname(dirname(__FILE__))))
      );

      $this->localize_block_data();

      register_block_type($this->blockName, [
          'editor_script' => 'pdf-insert-block-editor',
          'style' => 'pdf-insert-block-style',
          'render_callback' => [$this, 'renderBlock'],
          'attributes' => [
              'pdfUrl' => [
                  'type' => 'string',
                  'default' => '',
              ],
              'pdfTitle' => [
                  'type' => 'string',
                  'default' => '',
              ],
              'openInNewTab' => [
                  'type' => 'boolean',
                  'default' => true,
              ],
          ],
      ]);
  }

  public function renderBlock($attributes)
  {

      error_log('renderBlock : ' . print_r($attributes, true));
      if (empty($attributes['pdfUrl'])) {
          return '';
      }

      $title = !empty($attributes['buttonText']) ? $attributes['buttonText'] : 'PDF ファイル';
      $target = !empty($attributes['openInNewTab']) ? ' target="_blank"' : '';

      return sprintf(
          '<div class="wp-block-link-gallery-pdf-insert"><a href="%s"%s class="pdf-link"><span class="pdf-icon">📄</span> %s</a></div>',
          esc_url($attributes['pdfUrl']),
          $target,
          esc_html($title)
      );
  }
}
