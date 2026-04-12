@extends('app')


@section('head')
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.5.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.5.1/mapbox-gl.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('body')

<section id="map-app">
    <div class="map-container">

        <div class="" style="position: relative;">
            <div id="map"></div>

            <div class="search-container">
                <h5>Search Prisoners</h5>
                <div class="search-input px-2 py-2 d-flex">
                    <input type="text" id="query" class="form-control form-control-sm" placeholder="Search ...">
                    <button class="btn btn-outline-light btn-sm pull-right map-reset" id="map-reset">
                        <i class="fa fa-undo" aria-hidden="true"></i>
                    </button>
                </div>
                <!-- <div class="prisoners-count text-center">
                    100 Prisoners
                </div> -->
                <div class="suggestions list-group">

                </div>
            </div>

            <div class="info-content-wrapper" id="info-container">
                <div class="info-close-wrapper">
                    <div class="control-wrapper">
                        <button class="btn" id="prev-btn">Prev</button>
                        <span id="count"> 1 of 18 </span>
                        <button class="btn" id="next-btn">Next</button>
                    </div>
                    <button class="info-close" id="info-close" data-element="close" aria-label="Close info panel" aria-expanded="true">
                        <svg aria-hidden="true" role="presentation" focusable="false" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                    </button>
                </div>


                <article id="vessel-info"></article>
            </div>
        </div>
    </div>
</section>
    <!-- <script src="./photoswipe.umd.min.js"></script> -->
    <!-- <script src="./photoswipe-lightbox.umd.min.js"></script> -->
    <!-- <link rel="stylesheet" href="../photoswipe.css"> -->

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/5.4.4/photoswipe.esm.min.js" integrity="sha512-AyqbkQ0CCFXttmj38AAryPYIKEOdL6lApyzLje2dyvMwLoHv7PPXIeKS86gF4V85Gv+ZsCiOSP0yHaCXcemmaQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/5.4.4/photoswipe-lightbox.esm.min.js" integrity="sha512-S9RkWnGja84tXKFxTN7iLVP3pUCsnfqnF+0ZK2CSOhmCqa6lxoutHUoizBVnqCIsH8HW7e/3u9HEOOwlR01TLA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
     -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/5.4.4/photoswipe.min.css" integrity="sha512-LFWtdAXHQuwUGH9cImO9blA3a3GfQNkpF2uRlhaOpSbDevNyK1rmAjs13mtpjvWyi+flP7zYWboqY+8Mkd42xA==" crossorigin="anonymous" referrerpolicy="no-referrer" />


<script type="module">
    import PhotoSwipeLightbox from 'https://unpkg.com/photoswipe/dist/photoswipe-lightbox.esm.js';
    mapboxgl.accessToken = @json(config('services.mapbox.token', ''));
    const map = new mapboxgl.Map({
        container: 'map',
        // Choose from Mapbox's core styles, or make your own style with Mapbox Studio
        style: 'mapbox://styles/mapbox/dark-v11',
        projection:'albers',
        center: {lng: -97.81, lat: 39.14},
        zoom: 3.25
    });

    window.map = map;

    var prisoners = [];

    const navigationControl = new mapboxgl.NavigationControl({
        showCompass:false
    });

    map.addControl(navigationControl, "bottom-left");

    map.on('load', () => {
        map.addSource('prisoners', {
            type: 'geojson',
            // Point to GeoJSON data. This example visualizes all M1.0+ prisoners
            // from 12/22/15 to 1/21/16 as logged by USGS' Earthquake hazards program.
            data:{ "type":"FeatureCollection", "features":[]},
            // cluster: true,
            // clusterMaxZoom: 14, // Max zoom to cluster points on
            // clusterRadius: 50 // Radius of each cluster when clustering points (defaults to 50)
        });

        map.addLayer({
            id: 'clusters',
            type: 'circle',
            source: 'prisoners',
            filter: ['has', 'point_count'],
            paint: {
                'circle-color': '#fff',
                'circle-radius': [
                    'step',
                    ['get', 'point_count'],
                    15,
                    100,
                    30,
                    750,
                    40
                ]
            }
        });

        map.addLayer({
            id: 'cluster-count',
            type: 'symbol',
            source: 'prisoners',
            filter: ['has', 'point_count'],
            layout: {
                'text-field': ['get', 'point_count_abbreviated'],
                'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
                'text-size': 10
            }
        });

        map.addLayer({
            id: 'unclustered-point',
            type: 'circle',
            source: 'prisoners',
            filter: ['!', ['has', 'point_count']],
            paint: {
                'circle-color': 'orange',
                'circle-radius': 8,
                'circle-stroke-width': 1,
                'circle-opacity':1,
                'circle-stroke-color': '#ddd'
            }
        });

        // inspect a cluster on click
        map.on('click', 'clusters', (e) => {
            const features = map.queryRenderedFeatures(e.point, {
                layers: ['clusters']
            });
            const clusterId = features[0].properties.cluster_id;
            map.getSource('prisoners').getClusterExpansionZoom(
                clusterId,
                (err, zoom) => {
                    if (err) return;

                    map.easeTo({
                        center: features[0].geometry.coordinates,
                        zoom: zoom
                    });
                }
            );
        });

        map.on('click', 'unclustered-point', (e) => {
            let features = map.queryRenderedFeatures(e.point, {
                layers: ["unclustered-point"]
            });

            features = features.map(ft => ft.toJSON());

            let feature = e.features[0].toJSON();

            const coordinates =feature.geometry.coordinates.slice();

            map.setPaintProperty("unclustered-point", "circle-color", [
                'match',
                ['get', 'id'],
                feature.properties.id,
                'green',
                'orange',
            ]);
            let ids = features.map(ft =>  ft.properties.id);
            let prisonersAtLocation = window.prisoners.filter(vessel => ids.includes(vessel.id));

            let vessel = window.prisoners.find(vessel => vessel.id == feature.properties.id);
            let props = vessel;


            let images = []
            let popupContent = `<div class="popup-content">
                <div class="popup-header">
                    ${props.name}


                </div>
                <div class="popup-body">
                    <div class="images-section">
                       <img src="${props.Photo}" alt="" />
                    </div>
                </div>
            </div>`;

            renderCardContent(prisonersAtLocation);
            document.getElementById("info-container").style.display = "block";
            // new mapboxgl.Popup()
            //     .setLngLat(coordinates)
            //     .setHTML(
            //         popupContent
            //     )
            //     .addTo(map);
            initializeLightbox();
        });

        function handleMouseEnter() {
            map.getCanvas().style.cursor = 'pointer';
        }

        map.on('mouseenter', 'unclustered-point',  handleMouseEnter);
        map.on('mouseenter', 'clusters', handleMouseEnter);

        function handleMouseLeave() {
            map.getCanvas().style.cursor = '';
        }

        map.on('mouseleave', 'unclustered-point',  handleMouseLeave);
        map.on('mouseleave', 'clusters', handleMouseLeave);



        // load the data
        loadPrisoners();
    });

    function esc(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderCardContent(entries) {
        let contentEntries = entries.map(entry => {
            let props = {...entry};
            props.description = props.Description ? props.Description : "";
            props.Photo = props.Photo ? props.Photo : "";

            let content = `
                <div class="content">
                    <figure>
                        <div data-element="figure" data-visible="true">
                            ${props.Photo ? `<img src="${esc(props.Photo)}" alt="${esc(props.name || '')}" />` : ''}
                        </div>
                    </figure>

                    <header class="systems-dashboard-info__header">
                        <h2>
                            <span>${esc(props.name || '')}</span>
                        </h2>
                        <span></span>
                        <span>Race: ${esc(props.Race || '')}</span> <br>
                        <span>Age: ${props.Age && !props.Age.error ? `${esc(String(props.Age))} years` : 'Unknown'}</span> <br>
                    </header>

                    ${ props.calculatedPunishment ? `<section>
                        <div class="title">Calculated Punishment</div>
                        <div>${esc(props.calculatedPunishment)}</div>
                    </section>` : "" }

                    ${ props.Ideologies ? `<section>
                        <div class="title">Ideologies</div>
                        <div>${esc(props.Ideologies)}</div>
                    </section>` : "" }


                    ${ props.Affiliation ? `<section>
                        <div class="title">Affiliation</div>
                        <div>${esc(props.Affiliation)}</div>
                    </section>` : "" }

                    ${props.description ? `<section>
                        <div class="title">Description</div>
                        ${props.description.split("\n").map(p => `<p>${esc(p)}</p>`).join("")}
                    </section>` : ""}

                </div>
            `;

            return content;
        });

        let index = 0;
        let contentContainer = document.getElementById("vessel-info");

        function goToNext() {
            if(index < contentEntries.length - 1) {
                index += 1;
            } else {
                index = 0;
            }

            contentContainer.innerHTML = contentEntries[index];
            updateCount(index);
        }

        function goToPrev() {
            if(index > 0) {
                index -= 1;
            } else {
                index = contentEntries.length - 1;
            }

            contentContainer.innerHTML = contentEntries[index];
            updateCount(index);
        }

        function updateCount(index) {
            if(entries.length > 1) {
                document.getElementById("count").innerHTML = `${index + 1} of ${contentEntries.length}`;
                document.querySelector(".control-wrapper").style.display = "block";
            } else {
                document.querySelector(".control-wrapper").style.display = "none";
                document.getElementById("count").innerHTML = `${index + 1} of ${contentEntries.length}`;
            }
        }

        document.getElementById("next-btn").onclick = (e) => {
            goToNext();
        }

        document.getElementById("prev-btn").onclick = (e) => {
            goToPrev();
        }

        updateCount(index);
        contentContainer.innerHTML = contentEntries[index]
    }

    async function loadPrisoners() {
        let requestData = await fetch(`/api/prisoners`)
            .then(res => res.json());

        let data = []
        requestData.forEach((item) => {
            if(!item["In Custody"]) return false
            if(!item["Address"] || !item["latitude"] || !item["longitude"]) return false
            data.push(item)
        })

        window.allPrisoners = [...data];
        let prisonersGeocode = await geocodeResults(window.allPrisoners);

        window.prisoners = data.filter(entry => (entry.latitude && entry.longitude));
        window.prisoners = [...window.prisoners, ...prisonersGeocode];

        let prisonersFc = data.filter(entry => (entry.latitude && entry.longitude)).map(entry => {
            return {
                "type":"Feature",
                "geometry":{"type":"Point", "coordinates": [entry.longitude, entry.latitude]},
                "properties":{...entry}
            }
        });

        map.getSource("prisoners").setData({ "type":"FeatureCollection", "features":[...prisonersFc]});
        renderListItems(window.prisoners)
    }

    const debounce = (callback, wait) => {
        let timeoutId = null;
        return (...args) => {
            window.clearTimeout(timeoutId);
            timeoutId = window.setTimeout(() => {
                callback.apply(null, args);
            }, wait);
        };
    }

    const geocodeResults = async (prisoners) => {
        var targetPrisoners = prisoners.filter(prisoner => prisoner.cases.length).filter(lc => !lc.latitude);
        targetPrisoners = targetPrisoners.filter(prisoner => prisoner['Address']);
        let requests = targetPrisoners.map(prisoner => {
            let url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${prisoner['Address']}.json?limit=5&country=us&language=en-US&access_token=${mapboxgl.accessToken}`;

            return fetch(url);
        });

        try {
            let response = await Promise.all(requests).then(responses => Promise.all(responses.map(res => res.json())))
            response.forEach((fc, i) => {
                if(fc.features && fc.features.length) {
                    let [longitude, latitude] = fc.features[0].center;

                    targetPrisoners[i] = {...targetPrisoners[i], new_lat:latitude, new_lng:longitude }
                } else {

                }

            });

            return targetPrisoners;
            // console.log(response);
        } catch (error) {
            console.error(error);
            return [];
        }

    }

    const handleQueryInput = debounce((e) => {
        // Do stuff with the event!
        let { value } = e.target;

        if(value.length > 1) {
            filterPrisonersByName(value.toLocaleLowerCase());
        } else {
            renderListItems(window.prisoners);
            document.querySelector(".suggestions").innerHTML = "";
        }
    }, 250);

    function filterPrisonersByName(query) {
        let prisoners = window.prisoners.filter(prisoner => {
            return  query.split(" ").every(qentry => prisoner.name.toLocaleLowerCase().includes(qentry))
        });

        renderListItems(prisoners);
    }

    function renderListItems(prisoners) {
        document.querySelector(".suggestions").innerHTML = "";
        let docFrag = document.createDocumentFragment();

        let states = prisoners.map(prisoner => prisoner.State).map(state => {
            return !state ? "Others" : state;
        });

        states = [...new Set(states)];

        // Sort states alphabetically, placing 'Others' last
        states.sort((a, b) => {
            if (a === "Others") return 1; // 'Others' should come last
            if (b === "Others") return -1;
            return a.localeCompare(b); // Alphabetical sort
        });

        states.forEach(state => {
            let divContainer = document.createElement("div");
            divContainer.classList.add("title-section");

            divContainer.innerHTML = `<h3>${state}</h3>`;
            docFrag.appendChild(divContainer);

            const prisonersGroup = prisoners.filter(prisoner =>
                (state === "Others" && !prisoner.State) || prisoner.State === state
            );

            createListingItems(prisonersGroup, docFrag);
        });

        function createListingItems(prisonersGroup, docFrag) {
            prisonersGroup.forEach(prisoner => {
                let listItem = document.createElement("li");
                listItem.classList.add("list-group-item");

                let address = prisoner.cases[0];
                let punishment = prisoner.calculatedPunishment || prisoner.imprisonedFor;

                listItem.innerHTML = `<div class="item">
                <h5>${prisoner.name}</h5>
                <div>
                    ${prisoner.inmateNumber ? `<div>#${prisoner.inmateNumber} </div>` : ""}
                    ${address ? address['Mailing address'] ? `<div>P.O Box: ${address['Mailing address']} </div>` : "" : ""}
                </div>
                <div class="">
                    ${prisoner.Birthdate ? `<div>Birthday: ${prisoner.Birthdate}</div>` : ""}
                    ${punishment ? `<div>Imprisoned for: ${punishment}</div>` : ""}
                </div>
            </div>`;

                listItem.onclick = () => {
                    map.flyTo({
                        center: [prisoner.longitude, prisoner.latitude],
                        zoom: 10
                    });

                    renderCardContent([prisoner]);
                    document.getElementById("info-container").style.display = "block";
                };

                docFrag.append(listItem);
            });
        }

        document.querySelector(".suggestions").append(docFrag);
    }

    function queryPrisoners() {
        document.getElementById("query").addEventListener("input", handleQueryInput);

        document.getElementById("map-reset").onclick = (e) => {
            document.getElementById("query").value = "";
            document.querySelector(".suggestions").innerHTML = "";
            document.getElementById("info-container").style.display = "none";

            this._map.flyTo({
                center: {lng: -97.81, lat: 39.14},
                zoom: 3.25
            });
        }
    }

    queryPrisoners();


    document.getElementById("info-close").onclick = (e) => {
        document.getElementById("info-container").style.display = "none";

    }

    function initializeLightbox() {
        // const lightbox = new PhotoSwipeLightbox({
        //     gallery: '#gallery',
        //     children: 'a',
        //     pswpModule: PhotoSwipe
        // });

        // lightbox.init();

        const lightbox = new PhotoSwipeLightbox({
            gallery: '#gallery',
            children: 'a',
            pswpModule: () => import('https://unpkg.com/photoswipe'),
        });

        lightbox.init();
    }

    // control
    class HomeControl {
        onAdd(map) {
            this._map = map;
            this._container = document.createElement("div");
            this._container.className = "mapboxgl-ctrl";

            const homeBtn = document.createElement("button");
            homeBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>`;

            homeBtn.classList.add("home-btn");

            homeBtn.onclick = (e) => {
                document.getElementById("info-container").style.display = "none";

                this._map.flyTo({
                    center: {lng: -97.81, lat: 39.14},
                    zoom: 3.25
                });


            }

            this._container.append(homeBtn);
            return this._container;
        }

        onRemove() {
            this._container.parentNode.removeChild(this._container);
            this._map = undefined;
        }
    }

    map.addControl(new HomeControl(), "bottom-left");

    // https://tessadem.com/api/elevation?key=241e08998074b3df4b7048d3c6ec17c5c8b57f2a&locations=57.688709,11.976404
</script>
    @include('sections.faq', ['type'=>'map'])

@endsection
