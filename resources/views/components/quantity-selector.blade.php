@props(['value' => 1, 'id' => 'quantityInput'])

<div class="zyra-qty-stepper">
    <button type="button" class="zyra-qty-btn" onclick="const input = document.getElementById('{{ $id }}'); let v = parseInt(input.value) || 1; if(v > 1) input.value = v - 1;">
        <i class="bi bi-dash"></i>
    </button>
    <input type="text" id="{{ $id }}" class="zyra-qty-input" value="{{ $value }}" readonly>
    <button type="button" class="zyra-qty-btn" onclick="const input = document.getElementById('{{ $id }}'); let v = parseInt(input.value) || 1; input.value = v + 1;">
        <i class="bi bi-plus"></i>
    </button>
</div>
