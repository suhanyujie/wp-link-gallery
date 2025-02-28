<div class="link-gallery-widget">
    @if($links->isNotEmpty())
        <ul class="link-gallery-list">
            @foreach($links as $link)
                <li class="link-gallery-item">
                    <a href="{{ esc_url($link->url) }}" target="{{ esc_attr($link->target) }}" title="{{ esc_attr($link->description) }}">
                        @if($link->image)
                            <img src="{{ esc_url($link->image) }}" alt="{{ esc_attr($link->name) }}" class="link-gallery-image">
                        @endif
                        <span class="link-gallery-name">{{ esc_html($link->name) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <p class="link-gallery-empty">暂无友情链接</p>
    @endif
</div>