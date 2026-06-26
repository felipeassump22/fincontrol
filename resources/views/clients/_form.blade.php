<div class="form-group">
    <label class="form-label">{{ __('Nome do cliente') }}</label>
    <input type="text" name="name" value="{{ old('name', $client?->name) }}" placeholder="{{ __('Ex: Empresa Alpha Ltda') }}" required>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">{{ __('Tipo de documento') }}</label>
        <select name="document_type">
            <option value="">{{ __('— Selecione —') }}</option>
            <option value="CPF" {{ old('document_type', $client?->document_type) === 'CPF' ? 'selected' : '' }}>CPF</option>
            <option value="CNPJ" {{ old('document_type', $client?->document_type) === 'CNPJ' ? 'selected' : '' }}>CNPJ</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">{{ __('CPF/CNPJ') }}</label>
        <input type="text" name="document" value="{{ old('document', $client?->document) }}" placeholder="{{ __('Somente números') }}">
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">{{ __('CEP') }}</label>
        <input type="text" name="zip_code" id="{{ $prefix }}-zip" value="{{ old('zip_code', $client?->zip_code) }}" onblur="fetchAddressByCep('{{ $prefix }}-zip', '{{ $prefix }}')">
    </div>
    <div class="form-group">
        <label class="form-label">{{ __('Rua') }}</label>
        <input type="text" name="street" id="{{ $prefix }}-street" value="{{ old('street', $client?->street) }}">
    </div>
    <div class="form-group">
        <label class="form-label">{{ __('Número') }}</label>
        <input type="text" name="number" value="{{ old('number', $client?->number) }}">
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">{{ __('Complemento') }}</label>
        <input type="text" name="complement" value="{{ old('complement', $client?->complement) }}">
    </div>
    <div class="form-group">
        <label class="form-label">{{ __('Bairro') }}</label>
        <input type="text" name="neighborhood" id="{{ $prefix }}-neighborhood" value="{{ old('neighborhood', $client?->neighborhood) }}">
    </div>
    <div class="form-group">
        <label class="form-label">{{ __('Cidade') }}</label>
        <input type="text" name="city" id="{{ $prefix }}-city" value="{{ old('city', $client?->city) }}">
    </div>
    <div class="form-group">
        <label class="form-label">{{ __('UF') }}</label>
        <input type="text" name="state" id="{{ $prefix }}-state" maxlength="2" value="{{ old('state', $client?->state) }}">
    </div>
</div>
