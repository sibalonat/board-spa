<template>
    <div class="container">
        <div class="row">
            <div class="col8">
                KANBAN
                <span>Create a work environment with flexibility in mind</span>
            </div>
        </div>
        <div class="row marginTop">
            <div v-for="item in items" :key="item.id" class="item-span column">
                <p>{{ item.title }} <XIcon class="iconTitle" @click="deleteColumn(item.id)" /></p>
                <div class="cardsRow">
                    <button @click="onClickToOpenModal(item.id)">ADD CARD</button>
                </div>
                <!-- v-model="item.cards" -->
                <!-- draggable=".card-item" -->
                <draggable v-model="item.cards" handle=".card-item" :group="'item.cards'" :move="onMove" v-bind="dragOptions" @start="isDragging=true" @end="isDragging=false">
                    <transition-group type="transition">
                        <div class="card-item" v-for="card in item.cards" :key="card.id">
                            <div>
                                <p>{{ card.title }}</p>
                                <div class="grids">
                                    <PencilIcon class="iconTitle" @click="editCardProperties(item.id, card.id)" />
                                    <HandIcon class="iconTitle" />
                                </div>
                            </div>
                        </div>
                    </transition-group>
                </draggable>
            </div>
            <div class="item-span marginTop">
                <button @click="() => {
                    openTemplateForColumn === false ?
                    openTemplateForColumn = true :
                    openTemplateForColumn = false
                    }">
                    Add Column
                </button>
                <div v-if="openColumnTemplate" class="columnTitleCard">
                    <form @submit.prevent="saveColumnName">
                        Column Title
                        <input type="text" v-model="columnTitle">
                        <button type="submit">SAVE</button>
                    </form>
                </div>
            </div>
        </div>
        <template>
            <modal name="example" class="modal_style" :width="710" :height="380">
                <div class="wrapped">
                    <form @submit.prevent="saveCardProperties">
                        <div>
                            <label for="title">Title</label>
                            <input type="text" v-model="cardTitle">
                            <label for="description">Description</label>
                            <textarea id="description" v-model="cardDescription" cols="30" rows="6"></textarea>
                        </div>
                        <div class="button_wrapper">
                            <div>
                                <button type="button" class="cancel" @click="onClickToCloseModal">Cancel</button>
                            </div>
                            <div>
                                <button type="submit">Save Card</button>
                            </div>
                        </div>
                    </form>
                </div>
            </modal>
        </template>
    </div>
</template>

<script>
import { XIcon, HandIcon, PencilIcon   } from "@vue-hero-icons/solid"
import draggable from "vuedraggable";

export default {
    name: 'kanban',
    components: {
        XIcon,
        HandIcon,
        PencilIcon,
        draggable,
    },
    data() {
        return {
            columnIdCards: null,
            cardId: null,
            openColumnTemplate: false,
            columnTitle: '',
            cardTitle: '',
            cardDescription: '',
            items: [],
            headers: { 'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf_token"]').content },
            isDragging: false,
            delayedDragging: false,
            editable: true,
            dragItem: null,
            dragItemColumn: null
        }
    },
    created() {
        this.getAllColumnWithCards()
    },
    computed: {
        openTemplateForColumn: {
            get: function () {
                return this.openColumnTemplate;
            },
            set: function (newValue) {
                this.openColumnTemplate = newValue
            }
        },
        dragOptions() {
            return {
                animation: 0,
                disabled: !this.editable,
                ghostClass: "ghost"
            };
        },
    },
    methods: {
        getAllColumnWithCards() {
            axios.get(route('getcolumnwithcards'), this.headers)
            .then((response) => {
                this.items = response.data
            })
        },
        onClickToOpenModal(i) {
            this.$modal.show('example')
            this.columnIdCards = i
        },
        onClickToCloseModal() {
            this.$modal.hide('example')

            this.cardTitle = ''
            this.cardDescription = ''
            this.columnIdCards = null
        },
        editCardProperties(i, c) {
            console.log(c);
            // console.log(i);
            this.columnIdCards = i
            this.cardId = c
            this.$modal.show('example')
            axios.get(route('getcard', {column: this.columnIdCards, card: c}), this.headers)
            .then((response) => {
                console.log(response);
                this.cardTitle = response.data.title
                this.cardDescription = response.data.description
            })
        },
        saveColumnName() {
            axios.post(route('storecolumnname'), {title: this.columnTitle}, this.headers)
            .then(() => {
                this.openColumnTemplate = false
                this.columnTitle = ''
                this.getAllColumnWithCards()
            })
        },
        saveCardProperties() {

            if (this.cardId) {
                axios.put(route('updatecard', {column: this.columnIdCards, card: this.cardId}), {
                    title: this.cardTitle,
                    description: this.cardDescription,
                    column_id: this.columnIdCards
                }, this.headers)
                .then(() => {
                    this.openColumnTemplate = false
                    this.cardTitle = ''
                    this.cardDescription = ''
                    this.columnIdCards = null
                    this.$modal.hide('example')
                    this.getAllColumnWithCards()
                })
            } else {
                axios.post(route('storecard', this.columnIdCards), {
                    title: this.cardTitle,
                    description: this.cardDescription,
                    column_id: this.columnIdCards
                }, this.headers)
                .then(() => {
                    this.openColumnTemplate = false
                    this.cardTitle = ''
                    this.cardDescription = ''
                    this.columnIdCards = null
                    this.$modal.hide('example')
                    this.getAllColumnWithCards()
                })
            }
        },
        deleteColumn(id) {
            axios.delete(route('deletecolumn', id), this.headers)
            .then(() => {
                this.getAllColumnWithCards()
            })
        },
        onMove({ relatedContext, draggedContext }) {

            this.dragItemColumn = relatedContext.element.column_id
            this.dragItem = draggedContext.element.id

            setTimeout(() => {
                this.changeColumnName(this.dragItemColumn, this.dragItem)
            }, 400)

        },
        changeColumnName(column, card) {
            axios.put(route('updatecardparentid', {card: card}), {
                column_id: column
            }, this.headers)
            .then(() => {
                this.getAllColumnWithCards()
            })
        }
    },
    watch: {
        // items: (newDT) => {
        //     console.log(newDT);
        // },
        isDragging(newValue) {
            if (newValue) {
                this.delayedDragging = true;
                return;
            }
            this.$nextTick(() => {
                this.delayedDragging = false;
            });
        }
    }
}
</script>


<style lang="scss" scoped>
@import '../../sass/variables';

.container {
    width: 100%;
    .modal_style {
        position: fixed;
        width: 100vw;
        height: 100vh;
        top: 0;
        left: 0;
        background-color: rgba(0, 0, 0, 0.413);
        form {
            width: 100%;
        }
        .wrapped {
            display: flex;
            top: 30%;
            padding: 2rem;
            // width: 40vw;
            // height: 40vh;
            left: 30%;
            background-color: #f2f2f2;
            div {
                display: flex;
                flex-direction: column;
                input {
                    margin-top: .5rem;
                    padding: 8px 20px;
                    box-sizing: border-box;
                    border: 1px solid  honeydew;
                }
                textarea {
                    margin-top: .5rem;
                    padding: 8px 20px;
                    border: 1px solid  honeydew;
                }
                label {
                    margin-top: 1rem;
                    font-size: smaller;
                    font-weight: 300;
                }
            }
            .button_wrapper {
                display: flex;
                justify-content: space-between;
                flex-direction: row;
                div {
                    margin-top: $marginTop;
                    flex-basis: $base6Of12;


                    button {
                        width: $base11Of12;
                        margin: auto;
                        padding: .5rem .5rem .5rem .5rem;
                        margin-top: .5rem;
                        background: black;
                        color: honeydew;
                        border-radius: 12px;
                        border: none;
                    }
                    .cancel {
                        margin: auto;
                        margin-top: .5rem;
                        padding: .5rem .5rem .5rem .5rem;
                        background: transparent;
                        color: black;
                        border: 1px solid black;
                    }
                }
            }
        }

    }
}

.row {
    display: $flex;
    margin-top: $marginTop;
    justify-content: start;
    flex-wrap: wrap;

    .col8 {
        flex-basis: $base8Of12;
        font-size: xx-large;

        span {
            display: block;
            font-size: small;
            width: 20%;
            font-family: $font-family-dank-mono;
        }
    }
    .column {
        border-radius: 12px;
        border: 1px solid black;
        height: 70vh;
        margin-right: 3rem;

        p {
            text-align: start;
            background-color: black;
            color: honeydew;
            margin-top: 0;
            width: $headingWidth;
            display: flex;
            padding: 1rem 1rem;
            border-radius: 10px 10px 0 0;
            .iconTitle {
                margin-left: auto;
            }
        }

        .cardsRow {
            display: $flex;
            flex-direction: column;
            button {
                padding: 1.2rem .5rem .5rem .5rem;
                border-radius: 0;
                border: 1px solid honeydew;
                margin-top: -1rem;
            }
        }
        .card-item {
            display: $flex;
            width: $baseFull;
            justify-content: end;
            flex-direction: row;
            div {
                padding: 0 .5rem 0 0;
                display: grid;
                gap: 50px 20px;
                grid-template-columns: auto auto auto;
                .grids {
                    display: grid;
                    grid-template-columns: auto auto;
                    svg {
                        margin: auto 0;
                    }
                }

            }
            p {
                grid-column: 1 / span 2;
                text-align: left;
                width: 100%;
                background-color: transparent;
                color: black;
                margin-bottom: 0;
            }
        }
    }

    .marginTop {
        // margin-top: $marginTop;
        font-family: $font-family-dank-mono;

        button {
            width: $baseFull;
            padding: 1rem 0;
            background-color: transparent;
            border-radius: 12px;
            border-width: 1px;
        }

        .columnTitleCard {
            background-color: honeydew;
            margin-top: .5rem;
            border-top: 3px solid black;
            padding: .5rem 1rem;

            input {
                width: $straightEighty;
                margin-top: .5rem;
                padding: 8px 20px;
            }
            button {
                width: $baseFull;
                margin-top: .5rem;
                background: black;
                color: honeydew;
            }
        }
    }
}

@media (min-width: $layout-breakpoint-small) {
    .container {
        width: 640px;
    }
}

@media (min-width: $layout-breakpoint-medium) {
    .container {
        width: 768px;
    }
}

@media (min-width: $layout-breakpoint-large) {
    .container {
        width: 1024px;
    }
}

@media (min-width: $layout-breakpoint-xl-large) {
    .container {
        width: 1280px;
    }
}

@media (min-width: $layout-breakpoint-2xl-large) {
    .container {
        width: 1536px;
    }
}

.item-span {
    flex-basis: $base2Of12;
}
</style>
