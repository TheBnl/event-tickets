<template>
    <div class="checkin">
        <b-container>
            <b-row>
                <b-col>
                    <header class="checkin__header">
                        <h1>{{ title }} <span v-if="event">for {{ event.title }}</span></h1>
                    </header>
                    <section class="checkin__section checkin__section--form">
                        <b-alert v-if="showFormResponse" :variant="formResponseType" show>{{ formResponse.Message }}</b-alert>

                        <b-form @submit="onSubmit">
                                <label label class="sr-only" for="ticket-nr">Ticket #</label>
                                <b-input-group class="mt-3">
                                    <b-form-input
                                        id="ticket-nr"
                                        ref="ticketNr"
                                        v-model="form.ticket"
                                        type="text"
                                        placeholder="Ticket #"
                                        required
                                    ></b-form-input>
                                    <b-input-group-append>
                                        <b-button type="submit" variant="primary">Submit</b-button>
                                    </b-input-group-append>
                                </b-input-group>
                        </b-form>

                        <b-form-group
                            label="Zoeken"
                            label-for="filter-input"
                            class="mt-3"
                            >
                            <b-form-input
                                id="filter-input"
                                v-model="filter"
                                type="search"
                                placeholder="Zoek op naam of ticket"
                                >
                            </b-form-input>
                        </b-form-group>
                    </section>
                    <section class="checkin__section checkin__section--table" v-if="event">
                        <p>Checked in: {{ checkedinCount }}/{{ attendeesCount }}</p>
                        <b-table :fields="fields" :items="getAttendees" :filter="filter" ref="table">
                            <template #table-busy>
                                <div class="text-center my-2">
                                    <b-spinner class="align-middle"></b-spinner>
                                    <strong>Loading...</strong>
                                </div>
                            </template>
                            <template #cell(checkinLink)="row">
                                <b-button v-if="!row.item.checkedIn" block size="sm" variant="success" @click="checkin(row.item.ticketCode)" class="mr-1">
                                    Check in
                                </b-button>
                                <b-button v-if="row.item.checkedIn && row.item.allowCheckout" block size="sm" variant="danger" @click="checkin(row.item.ticketCode)" class="mr-1">
                                    Check out
                                </b-button>
                            </template>
                        </b-table>
                    </section>
                </b-col>
            </b-row>
        </b-container>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            title: 'Checkin',
            form: {
                ticket: '',
            },
            formResponse: {},
            fields: [],
            filter: null,
            attendees: []
        }
    },
    methods: {
        checkin(ticket) {
            console.log('checkin', ticket);

            const promise = axios.get(window.location.origin + '/checkin/ticket/' + ticket, {
                headers: {
                    // request as ajax
                    'x-requested-with': 'XMLHttpRequest'
                }
            });

            promise.then(data => {
                this.formResponse = data.data;
                console.log('validate ticket', data);
                this.$refs.table.refresh()
                this.$refs.ticketNr.focus();
            }).catch(err => {
                console.error('validate ticket::error', err);
                this.$refs.table.refresh()
                this.$refs.ticketNr.focus();
            });
        },
        onSubmit(event) {
            event.preventDefault();
            this.checkin(this.form.ticket);
            this.form.ticket = '';
        },
        handleFilter(row, filter) {
            console.log('handleFilter:', filter, 'row', row);
        },
        getAttendees(ctx, callback) {
            if (!this.event) {
                callback([]);
            }

            console.log('ctx', ctx);

            const url = new URL(window.location.origin);
            url.pathname = '/checkin/attendees/' + this.event.id;
            
            // add the filter
            if (ctx.filter) {
                url.searchParams.append('filter', ctx.filter);
            }
            
            // add the cachebuster
            const time = new Date().valueOf();
            url.searchParams.append('cb', time);

            console.log('build url', url.href);

            axios.get(url.href).then(data => {
                this.attendees = data.data.attendees;
                callback(this.attendees || []);
            }).catch(err => {
                console.error('attendees::error', err);
                callback([]);
            })
        }
    },
    computed: {
        attendeesCount() {
            return this.attendees.length;
        },
        checkedinCount() {
            return this.attendees.filter(attedee => Boolean(attedee?.checkedIn) === true).length
        },
        event() {
            return window.hasOwnProperty('event') ? window.event : null;
        },
        showFormResponse() {
            return this.formResponse.hasOwnProperty('Message');
        },
        formResponseType() {
            if (this.formResponse.hasOwnProperty('Code')) {
                switch (this.formResponse.Code) {
                    case VALIDATION_STATUS.CHECKED_IN:
                    case VALIDATION_STATUS.ALREADY_CHECKED_IN:
                        return 'success';
                    case VALIDATION_STATUS.CHECKED_OUT:
                        return 'warning';
                    case VALIDATION_STATUS.CODE_NOT_FOUND:
                    case VALIDATION_STATUS.NO_CODE:
                    case VALIDATION_STATUS.TICKET_CANCELLED:
                    case VALIDATION_STATUS.ERROR:
                        return 'danger';
                    default:
                    case VALIDATION_STATUS.PENDING:
                       return 'primary';
                }
            }
            return 'primary';
        }
    },
    mounted() {
        let tableFields = window.tableFields;
        if (!tableFields) {
            tableFields = [
                { key: 'ticketCode', label: 'Ticket' },
                { key: 'name', label: 'Name' },
                { key: 'checkedInNice', label: 'Checked In' },
                { key: 'checkinLink', label: '' },
            ]
        }

        this.fields = tableFields;
        console.log('this.fields', this.fields);
        this.$refs.ticketNr.focus();
    },
    components: {},
}

// possible validation results
const VALIDATION_STATUS = {
  IDLE: 'IDLE',
  PENDING: 'PENDING',
  CHECKED_IN: 'MESSAGE_CHECK_IN_SUCCESS',
  CHECKED_OUT: 'MESSAGE_CHECK_OUT_SUCCESS',
  ALREADY_CHECKED_IN: 'MESSAGE_ALREADY_CHECKED_IN',
  CODE_NOT_FOUND: 'MESSAGE_CODE_NOT_FOUND',
  TICKET_CANCELLED: 'MESSAGE_TICKET_CANCELLED',
  NO_CODE: 'MESSAGE_NO_CODE',
  ERROR: 'ERROR'
};
</script>

<style lang="scss">
    .checkin {
        &__header,
        &__section {
            margin: 2rem 0;
        }
    }
    .form-inline {
        display: flex;
        width: 100%;
    }
    .btn-block {
        display: block;
        width: 100%;
    }
</style>