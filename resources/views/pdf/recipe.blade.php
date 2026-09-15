<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $recipe->title }} - Recipe Card</title>
    <style>
        @page {
            size: a5 portrait;
            margin: 8mm 10mm;
        }

        * {
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        body {
            font-size: 8.5pt;
            line-height: 1.35;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        /* Header Bar */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .header-brand {
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #64748b;
        }

        .header-badge {
            font-size: 7pt;
            font-weight: bold;
            padding: 2px 7px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            text-align: right;
        }

        /* Title & Summary */
        .recipe-title {
            font-size: 15pt;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 4px 0;
            line-height: 1.15;
            letter-spacing: -0.02em;
        }

        .recipe-summary {
            font-size: 8pt;
            color: #475569;
            margin: 0 0 8px 0;
            line-height: 1.3;
        }

        /* Meta Chips Bar */
        .meta-table {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 10px;
            padding: 6px 4px;
            text-align: center;
        }

        .meta-label {
            font-size: 6.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            display: block;
        }

        .meta-value {
            font-size: 8.5pt;
            font-weight: 700;
            color: #0f172a;
        }

        /* Layout Columns */
        .content-table {
            width: 100%;
            border-collapse: collapse;
        }

        .content-td-left {
            width: 38%;
            vertical-align: top;
            padding-right: 8px;
        }

        .content-td-right {
            width: 62%;
            vertical-align: top;
            padding-left: 8px;
            border-left: 1px solid #e2e8f0;
        }

        /* Section Headings */
        .section-heading {
            font-size: 8.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #0f172a;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 2px;
            margin: 0 0 6px 0;
        }

        /* Ingredients List */
        .ingredient-item {
            padding: 3px 0;
            border-bottom: 1px dashed #f1f5f9;
            font-size: 8pt;
        }

        .ingredient-qty {
            font-weight: 700;
            color: #0f172a;
        }

        .ingredient-name {
            color: #334155;
        }

        /* Instructions List */
        .step-item {
            margin-bottom: 7px;
            position: relative;
        }

        .step-number {
            display: inline-block;
            width: 14px;
            height: 14px;
            background: #0f172a;
            color: #ffffff;
            font-size: 6.5pt;
            font-weight: bold;
            line-height: 14px;
            text-align: center;
            border-radius: 50%;
            margin-right: 4px;
            vertical-align: middle;
        }

        .step-title {
            font-weight: 700;
            font-size: 8pt;
            color: #0f172a;
            display: inline;
        }

        .step-desc {
            font-size: 7.8pt;
            color: #334155;
            margin: 2px 0 0 18px;
            line-height: 1.3;
        }

        /* Nutrition box */
        .nutrition-table {
            width: 100%;
            margin-top: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 7pt;
            text-align: center;
            padding: 4px;
        }

        /* Chef Notes */
        .chef-notes {
            margin-top: 8px;
            background: #fefce8;
            border: 1px solid #fef08a;
            border-radius: 4px;
            padding: 5px 8px;
            font-size: 7.5pt;
            color: #713f12;
            line-height: 1.3;
        }

        /* Footer */
        .footer-table {
            width: 100%;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #e2e8f0;
            font-size: 6.5pt;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    {{-- Top Bar --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="header-brand">
                {{ $appName }} &bull; Chef's Collection
            </td>
            <td align="right">
                @if($order)
                    <span class="header-badge">Unlocked with Order #{{ $order->id }}</span>
                @else
                    <span class="header-badge">Official Recipe Card</span>
                @endif
            </td>
        </tr>
    </table>

    {{-- Title & Summary --}}
    <h1 class="recipe-title">{{ $recipe->title }}</h1>
    @if($recipe->summary)
        <p class="recipe-summary">{{ $recipe->summary }}</p>
    @endif

    {{-- Metadata Row (format) --}}
    <table class="meta-table" cellpadding="0" cellspacing="0">
        <tr>
            <td width="20%">
                <span class="meta-label">Prep Time</span>
                <span class="meta-value">{{ $recipe->prep_time ? $recipe->prep_time . ' min' : '15 min' }}</span>
            </td>
            <td width="20%">
                <span class="meta-label">Cook Time</span>
                <span class="meta-value">{{ $recipe->cook_time ? $recipe->cook_time . ' min' : '20 min' }}</span>
            </td>
            <td width="20%">
                <span class="meta-label">Total Time</span>
                <span class="meta-value">{{ $recipe->total_time ? $recipe->total_time . ' min' : '35 min' }}</span>
            </td>
            <td width="20%">
                <span class="meta-label">Servings</span>
                <span class="meta-value">{{ $recipe->servings ?? 2 }} portions</span>
            </td>
            <td width="20%">
                <span class="meta-label">Difficulty</span>
                <span class="meta-value">{{ ucfirst($recipe->difficulty ?? 'Medium') }}</span>
            </td>
        </tr>
    </table>

    {{-- 2-Column Content Body --}}
    <table class="content-table" cellpadding="0" cellspacing="0">
        <tr>
            {{-- Left Column: Ingredients & Nutrition --}}
            <td class="content-td-left">
                <div class="section-heading">Ingredients</div>

                @if(!empty($recipe->ingredients) && is_array($recipe->ingredients))
                    @foreach($recipe->ingredients as $ingredient)
                        <div class="ingredient-item">
                            <span class="ingredient-qty">
                                {{ $ingredient['quantity'] ?? '' }} {{ $ingredient['unit'] ?? '' }}
                            </span>
                            <span class="ingredient-name">
                                {{ $ingredient['item'] ?? ($ingredient['name'] ?? 'Ingredient') }}
                            </span>
                        </div>
                    @endforeach
                @else
                    <div class="ingredient-item">Standard pantry ingredients required.</div>
                @endif

                {{-- Nutrition Info if provided --}}
                @if(!empty($recipe->nutritional_info) && is_array($recipe->nutritional_info))
                    <div style="margin-top: 10px;" class="section-heading">Nutrition (per serving)</div>
                    <table class="nutrition-table" cellpadding="2" cellspacing="0">
                        <tr>
                            <td><strong>Cal:</strong> {{ $recipe->nutritional_info['calories'] ?? ($recipe->calories ?? '—') }}</td>
                            <td><strong>Protein:</strong> {{ $recipe->nutritional_info['protein'] ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Carbs:</strong> {{ $recipe->nutritional_info['carbs'] ?? '—' }}</td>
                            <td><strong>Fat:</strong> {{ $recipe->nutritional_info['fat'] ?? '—' }}</td>
                        </tr>
                    </table>
                @elseif($recipe->calories)
                    <table class="nutrition-table" cellpadding="2" cellspacing="0">
                        <tr>
                            <td><strong>Calories:</strong> {{ $recipe->calories }} kcal</td>
                        </tr>
                    </table>
                @endif

                @if($recipe->content)
                    <div class="chef-notes">
                        <strong>Chef's Note:</strong><br>
                        {{ Str::limit(strip_tags($recipe->content), 180) }}
                    </div>
                @endif
            </td>

            {{-- Right Column: Step-by-Step Instructions --}}
            <td class="content-td-right">
                <div class="section-heading">Directions & Instructions</div>

                @if(!empty($recipe->instructions) && is_array($recipe->instructions))
                    @foreach($recipe->instructions as $index => $instruction)
                        @php
                            $stepNum = $instruction['step'] ?? ($index + 1);
                            $stepTitle = $instruction['title'] ?? null;
                            $stepText = $instruction['text'] ?? ($instruction['description'] ?? (is_string($instruction) ? $instruction : ''));
                        @endphp
                        <div class="step-item">
                            <span class="step-number">{{ $stepNum }}</span>
                            @if($stepTitle)
                                <div class="step-title">{{ $stepTitle }}</div>
                            @endif
                            <div class="step-desc">{{ $stepText }}</div>
                        </div>
                    @endforeach
                @else
                    <div class="step-item">
                        <span class="step-number">1</span>
                        <div class="step-desc">Prepare and measure all ingredients according to the proportions above.</div>
                    </div>
                    <div class="step-item">
                        <span class="step-number">2</span>
                        <div class="step-desc">Follow standard culinary techniques for the best results and enjoy your fresh homemade creation!</div>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Page Footer --}}
    <table class="footer-table" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                Printed from {{ $appName }} &bull; Recipe Card
            </td>
            <td align="right">
                Downloaded on {{ now()->format('M d, Y') }}
            </td>
        </tr>
    </table>

</body>
</html>
