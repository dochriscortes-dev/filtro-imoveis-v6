<?php
/**
 * Plugin Name: Advanced Pods AJAX Filter V6
 * Description: A custom search plugin with a horizontal sticky bar and overlay modal layout.
 * Version: 6.0
 * Author: Jules
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Advanced_Pods_AJAX_Filter {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_shortcode( 'advanced_pods_ajax_filter', array( $this, 'render_search_form' ) );

		// AJAX hooks for City -> Neighborhood
		add_action( 'wp_ajax_apaf_get_neighborhoods', array( $this, 'get_neighborhoods' ) );
		add_action( 'wp_ajax_nopriv_apaf_get_neighborhoods', array( $this, 'get_neighborhoods' ) );
	}

	public function enqueue_assets() {
		wp_enqueue_style(
			'apaf-style',
			plugin_dir_url( __FILE__ ) . 'style.css',
			array(),
			'6.0.0'
		);

		wp_enqueue_script(
			'apaf-script',
			plugin_dir_url( __FILE__ ) . 'script.js',
			array( 'jquery' ),
			'6.0.0',
			true
		);

		wp_localize_script( 'apaf-script', 'apaf_obj', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'apaf_nonce' )
		));
	}

	public function render_search_form() {
		ob_start();
		?>
		<div id="apaf-search-bar-wrapper">
			<form id="apaf-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
				<!-- State A: Sticky Bar -->
				<div id="apaf-search-bar">

					<!-- Toggle Buy/Rent -->
					<div class="apaf-toggle-group">
						<label>
							<input type="radio" name="listing_type" value="buy" checked> Comprar
						</label>
						<label>
							<input type="radio" name="listing_type" value="rent"> Alugar
						</label>
					</div>

					<!-- City -->
					<select name="city" id="apaf-city">
						<option value="">Cidade</option>
						<option value="sao-paulo">São Paulo</option>
						<option value="rio-de-janeiro">Rio de Janeiro</option>
						<!-- Populate dynamically in real scenario -->
					</select>

					<!-- Neighborhood -->
					<select name="neighborhood" id="apaf-neighborhood" disabled>
						<option value="">Bairro</option>
						<!-- Populated via AJAX -->
					</select>

					<!-- Advanced Filters Link -->
					<a href="#" id="apaf-advanced-filters-trigger">Filtros Avançados</a>

					<!-- Search Button -->
					<button type="submit" id="apaf-submit-btn">Buscar</button>
				</div>

				<!-- State B: Modal -->
				<div id="apaf-modal" style="display: none;">
					<div class="apaf-modal-content">
						<span id="apaf-modal-close">&times;</span>
						<h3>Filtros Avançados</h3>

						<!-- Zone: Dropdown -->
						<div class="apaf-field-group">
							<label for="apaf-zone">Zona</label>
							<select name="zone" id="apaf-zone">
								<option value="">Selecione</option>
								<option value="urban">Urbana</option>
								<option value="rural">Rural</option>
							</select>
						</div>

						<!-- Property Type: Grid of Checkboxes -->
						<div class="apaf-field-group">
							<label>Tipo de Imóvel</label>
							<div class="apaf-checkbox-grid">
								<label><input type="checkbox" name="property_type[]" value="apartment"> Apartamento</label>
								<label><input type="checkbox" name="property_type[]" value="house"> Casa</label>
								<label><input type="checkbox" name="property_type[]" value="condo"> Condomínio</label>
								<label><input type="checkbox" name="property_type[]" value="land"> Terreno</label>
							</div>
						</div>

						<!-- Specs: Bedrooms -->
						<div class="apaf-field-group">
							<label>Quartos</label>
							<input type="hidden" name="bedrooms" id="apaf-bedrooms-input">
							<div class="apaf-specs-row" data-input="#apaf-bedrooms-input">
								<div class="spec-btn" data-value="1">1</div>
								<div class="spec-btn" data-value="2">2</div>
								<div class="spec-btn" data-value="3">3</div>
								<div class="spec-btn" data-value="4+">4+</div>
							</div>
						</div>

						<!-- Specs: Baths -->
						<div class="apaf-field-group">
							<label>Banheiros</label>
							<input type="hidden" name="baths" id="apaf-baths-input">
							<div class="apaf-specs-row" data-input="#apaf-baths-input">
								<div class="spec-btn" data-value="1">1</div>
								<div class="spec-btn" data-value="2">2</div>
								<div class="spec-btn" data-value="3">3</div>
								<div class="spec-btn" data-value="4+">4+</div>
							</div>
						</div>

						<!-- Specs: Garages -->
						<div class="apaf-field-group">
							<label>Vagas</label>
							<input type="hidden" name="garages" id="apaf-garages-input">
							<div class="apaf-specs-row" data-input="#apaf-garages-input">
								<div class="spec-btn" data-value="1">1</div>
								<div class="spec-btn" data-value="2">2</div>
								<div class="spec-btn" data-value="3">3</div>
								<div class="spec-btn" data-value="4+">4+</div>
							</div>
						</div>

						<!-- Price: Slider + Inputs -->
						<div class="apaf-field-group">
							<label>Preço</label>
							<div class="apaf-price-inputs">
								<input type="number" name="min_price" placeholder="Mínimo">
								<input type="number" name="max_price" placeholder="Máximo">
							</div>
							<input type="range" min="0" max="10000000" step="1000" id="apaf-price-slider">
						</div>

						<button type="button" id="apaf-apply-filters">Aplicar Filtros</button>
					</div>
					<div class="apaf-modal-overlay"></div>
				</div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	public function get_neighborhoods() {
		// Verify nonce for security
		// check_ajax_referer( 'apaf_nonce', 'nonce' );

		$city = isset( $_GET['city'] ) ? sanitize_text_field( $_GET['city'] ) : '';

		$neighborhoods = array();

		// Simulation of logic
		if ( 'sao-paulo' === $city ) {
			$neighborhoods = array(
				'moema' => 'Moema',
				'pinheiros' => 'Pinheiros',
				'jardins' => 'Jardins'
			);
		} elseif ( 'rio-de-janeiro' === $city ) {
			$neighborhoods = array(
				'copacabana' => 'Copacabana',
				'ipanema' => 'Ipanema',
				'leblon' => 'Leblon'
			);
		}

		wp_send_json_success( $neighborhoods );
	}
}

new Advanced_Pods_AJAX_Filter();
