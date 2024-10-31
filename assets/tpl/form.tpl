<input type="hidden" name="gns_option[add]" value="no" />
<input type="checkbox" value="yes" name="gns_option[add]" id="gns_add"{add} />
<label for="gns_add">Add this post to sitemap?</label>

<hr />

<label for="gns_access">Select post access type:</label>
<select name="gns_option[access]" id="gns_access" class="widefat">{access}</select>

<hr />

<label for="gns_genres">Genres:</label>
<select name="gns_option[genres][]" id="gns_genres" class="widefat" multiple>{genres}</select>

<hr />

<label for="gns_lang">Language:</label>
<select name="gns_option[lang]" id="gns_lang" class="widefat" reqired>{lang}</select>

<hr />

<label for="gns_stock">Type stock tickers (separated by comma):</label>
<input type="text" name="gns_option[stock]" id="gns_stock" class="widefat" placeholder="Stock tickers" value="{stock}" />

<hr />

<label for="gns_loc">Geolocations data:</label>
<input type="text" name="gns_option[loc]" id="gns_loc" class="widefat" placeholder="[City], [State/Province], [Country]" value="{loc}" />