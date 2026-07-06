<form method="GET" class="lags-filters">
    <div class="wrap">


        <input type="hidden" name="page" value="lags-messages">

        <div class="tablenav top">

            <!-- LEFT SIDE (filters) -->
            <div class="alignleft actions">

                <select name="status" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="unread" <?php selected($_GET['status'] ?? '', 'unread'); ?>>
                        Unread
                    </option>
                    <option value="read" <?php selected($_GET['status'] ?? '', 'read'); ?>>
                        Read
                    </option>
                </select>

            </div>

            <!-- RIGHT SIDE (search) -->
            <p class="search-box">
                <label class="screen-reader-text" for="lags-search-input">
                    Search Messages:
                </label>

                <input
                    type="search"
                    id="lags-search-input"
                    name="s"
                    value="<?php echo esc_attr($_GET['s'] ?? ''); ?>"
                    placeholder="Search messages...">

                <input
                    type="submit"
                    class="button"
                    value="Search">
            </p>

            <br class="clear">

        </div>
    </div>
</form>