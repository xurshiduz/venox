                    <?php
                    $link_limit = 8;
                    ?>

                    <nav class="mt-3">
                    @if ($data->lastPage() > 1)
                        <ul class="pagination">
                            <li class="page-item {{ ($data->currentPage() == 1) ? ' disabled' : '' }}">
                                <a class="page-link" href="{{ $data->url(1) }}" tabindex="-1" aria-disabled="true">{{ trans('backend.menu.nav_prev') }}.</a>
                             </li>
                            @for ($i = 1; $i <= $data->lastPage(); $i++)
                                <?php
                                $half_total_links = floor($link_limit / 2);
                                $from = $data->currentPage() - $half_total_links;
                                $to = $data->currentPage() + $half_total_links;
                                if ($data->currentPage() < $half_total_links) {
                                   $to += $half_total_links - $data->currentPage();
                                }
                                if ($data->lastPage() - $data->currentPage() < $half_total_links) {
                                    $from -= $half_total_links - ($data->lastPage() - $data->currentPage()) - 1;
                                }
                                ?>
                                @if ($from < $i && $i < $to)
                                    <li class="{{ ($data->currentPage() == $i) ? ' active' : '' }}" style="font-weight: {{ ($data->currentPage() == $i) ? 'bold' : '' }}">
                                        <a class="page-link" href="{{ $data->url($i) }}">{{ $i }}</a>
                                    </li>
                                @endif
                            @endfor
                            <li class="page-item {{ ($data->currentPage() == $data->lastPage()) ? ' disabled' : '' }}">
                                <a class="page-link" href="{{ $data->url($data->lastPage()) }}">{{ trans('backend.menu.nav_last') }}.</a>
                            </li>
                        </ul>
                    @endif
                    </nav>