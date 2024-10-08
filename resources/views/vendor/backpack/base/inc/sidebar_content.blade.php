{{-- This file is used to store sidebar items, inside the Backpack admin panel --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i>
                {{ trans('backpack::base.dashboard') }}</a></li>

<li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-group"></i>
                Users</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('instructor') }}"><i
                        class="nav-icon la la-mortar-board"></i> Instructors</a></li>


<li class="nav-item nav-dropdown">
        <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-sitemap"></i> Tracks</a>
        <ul class="nav-dropdown-items">
                <li class="nav-item"><a class="nav-link" href="{{ backpack_url('track') }}"><i
                                        class="nav-icon la la-list-alt"></i>
                                Tracks</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ backpack_url('track-section') }}"><i
                                        class="nav-icon la la-list-alt"></i>
                                Track Sections</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ backpack_url('track-item') }}"><i
                                        class="nav-icon la la-list-alt"></i>
                                Track Items</a></li>
        </ul>
</li>



<li class="nav-item"><a class="nav-link" href="{{ backpack_url('challenge') }}"><i class="nav-icon la la-terminal"></i>
                Challenges</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('lesson') }}"><i class="nav-icon la la-tv"></i>
                Lessons</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('workshop') }}"><i class="nav-icon la la-cog"></i>
                Workshops</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('challenge-user') }}"><i
                        class="nav-icon la la-terminal"></i> <span>Submissões</span></a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('tag') }}"><i class="nav-icon la la-tag"></i> Tags</a>
</li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('elfinder') }}"><i class="nav-icon la la-files-o"></i>
                <span>{{ trans('backpack::crud.file_manager') }}</span></a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('blog-post') }}"><i
                        class="nav-icon la la-rss-square"></i> Blog posts</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('technical-assessment') }}"><i
                        class="nav-icon la la-question"></i> Technical assessments</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('testimonial') }}"><i
                        class="nav-icon la la-question"></i> Testimonials</a></li>


<li class="nav-item nav-dropdown">
        <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-money"></i> Subscriptions</a>
        <ul class="nav-dropdown-items">
                <li class="nav-item"><a class="nav-link" href="{{ backpack_url('subscription') }}"><i
                                        class="nav-icon la la-list-alt"></i>
                                Assinaturas</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ backpack_url('coupon') }}"><i
                                        class="nav-icon la la-list-alt"></i>
                                Cupons</a></li>

        </ul>
</li>



<li class="nav-item"><a class="nav-link" href="{{ backpack_url('certificate') }}"><i
                        class="nav-icon la la-certificate"></i> Certificados</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('comment') }}"><i class="nav-icon la la-weixin"></i>
                Comentários</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('elfinder') }}"><i class="nav-icon la la-files-o"></i>
                <span>{{ trans('backpack::crud.file_manager') }}</span></a></li>