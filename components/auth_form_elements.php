<?php
/**
 * Auth Form Elements Component
 * Reusable form elements for authentication pages
 */

/**
 * Renders an error message
 * @param string $error - Error message to display
 */
function renderError($error) {
    if (!empty($error)): ?>
        <div class="p-3 rounded-lg mb-6 bg-red-500/10 border border-red-500/30 text-red-300 animate-slideIn text-sm">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif;
}

/**
 * Renders a success message
 * @param string $success - Success message to display
 */
function renderSuccess($success) {
    if (!empty($success)): ?>
        <div class="p-3 rounded-lg mb-6 bg-green-500/10 border border-green-500/30 text-green-300 animate-slideIn text-sm">
            <i class="fas fa-check-circle mr-2"></i>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
    <?php endif;
}

/**
 * Renders a text input field
 * @param array $config - Field configuration
 */
function renderInputField($config) {
    $defaults = [
        'type' => 'text',
        'required' => false,
        'icon' => 'fas fa-user',
        'placeholder' => '',
        'value' => '',
        'class' => '',
        'help_text' => '',
        'show_toggle' => false
    ];
    
    $field = array_merge($defaults, $config);
    $inputId = $field['name'];
    $toggleId = $field['show_toggle'] ? 'toggle' . ucfirst($field['name']) : '';
?>
    <div>
        <label for="<?php echo $inputId; ?>" class="block text-lighterGray text-sm font-medium mb-2">
            <?php echo $field['label']; ?>
            <?php if (isset($field['optional']) && $field['optional']): ?>
                <span class="text-lightGray font-normal">(Optional)</span>
            <?php endif; ?>
        </label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-lightGray">
                <i class="<?php echo $field['icon']; ?> text-sm"></i>
            </div>
            <input 
                type="<?php echo $field['type']; ?>" 
                id="<?php echo $inputId; ?>" 
                name="<?php echo $field['name']; ?>" 
                placeholder="<?php echo $field['placeholder']; ?>" 
                <?php echo $field['required'] ? 'required' : ''; ?>
                <?php echo isset($field['minlength']) ? 'minlength="' . $field['minlength'] . '"' : ''; ?>
                value="<?php echo htmlspecialchars($field['value']); ?>"
                class="w-full pl-10 <?php echo $field['show_toggle'] ? 'pr-12' : 'pr-4'; ?> py-3 bg-gray-800/80 border border-gray-600/50 rounded-lg text-white placeholder-gray-400 transition-all focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 <?php echo $field['class']; ?>">
            
            <?php if ($field['show_toggle']): ?>
            <button type="button" id="<?php echo $toggleId; ?>" class="absolute inset-y-0 right-0 pr-3 flex items-center text-lightGray hover:text-primary transition-colors">
                <i class="fas fa-eye text-sm"></i>
            </button>
            <?php endif; ?>
        </div>
        <?php if ($field['help_text']): ?>
        <p class="text-xs text-lightGray mt-1"><?php echo $field['help_text']; ?></p>
        <?php endif; ?>
    </div>
<?php
}

/**
 * Renders a submit button
 * @param array $config - Button configuration
 */
function renderSubmitButton($config) {
    $defaults = [
        'text' => 'Submit',
        'icon' => 'fas fa-check',
        'id' => 'submitBtn',
        'class' => ''
    ];
    
    $button = array_merge($defaults, $config);
?>
    <button type="submit" id="<?php echo $button['id']; ?>" class="w-full py-3 bg-gradient-to-r from-primary to-primaryDark text-white font-semibold rounded-lg transition-all hover:shadow-lg hover:shadow-primary/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-primary/50 disabled:opacity-50 disabled:cursor-not-allowed <?php echo $button['class']; ?>">
        <i class="<?php echo $button['icon']; ?> mr-2"></i>
        <?php echo $button['text']; ?>
    </button>
<?php
}

/**
 * Renders a link with consistent styling
 * @param array $config - Link configuration
 */
function renderAuthLink($config) {
    $defaults = [
        'class' => 'text-primary font-medium hover:text-primaryDark hover:underline transition-colors'
    ];
    
    $link = array_merge($defaults, $config);
?>
    <a href="<?php echo $link['url']; ?>" class="<?php echo $link['class']; ?>">
        <?php echo $link['text']; ?>
    </a>
<?php
}

/**
 * Renders the page header section
 * @param array $config - Header configuration
 */
function renderAuthHeader_Section($config) {
    $defaults = [
        'icon' => 'fas fa-chart-line',
        'title' => 'StepCashier',
        'subtitle' => '',
        'description' => '',
        'badge' => null
    ];
    
    $header = array_merge($defaults, $config);
?>
    <!-- Header Section -->
    <div class="px-8 pt-<?php echo isset($header['padding_top']) ? $header['padding_top'] : '10'; ?> pb-<?php echo isset($header['padding_bottom']) ? $header['padding_bottom'] : '8'; ?> text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-gradient-to-br from-primary to-primaryDark text-white text-2xl shadow-lg shadow-primary/30 mb-<?php echo isset($header['margin_bottom']) ? $header['margin_bottom'] : '6'; ?>">
            <i class="<?php echo $header['icon']; ?>"></i>
        </div>
        <h1 class="text-2xl font-bold text-primary mb-2"><?php echo $header['title']; ?></h1>
        <?php if ($header['subtitle']): ?>
        <h2 class="text-xl font-semibold text-white mb-1"><?php echo $header['subtitle']; ?></h2>
        <?php endif; ?>
        <?php if ($header['description']): ?>
        <p class="text-lightGray text-sm"><?php echo $header['description']; ?></p>
        <?php endif; ?>
        <?php if ($header['badge']): ?>
        <div class="mt-3 inline-flex items-center px-3 py-1 bg-primary/10 border border-primary/30 rounded-full text-primary text-xs font-medium">
            <i class="fas fa-<?php echo $header['badge']['icon']; ?> mr-1"></i>
            <?php echo $header['badge']['text']; ?>
        </div>
        <?php endif; ?>
    </div>
<?php
}
?>