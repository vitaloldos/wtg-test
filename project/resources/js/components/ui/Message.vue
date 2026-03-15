<script setup lang="ts">
import {onMounted, onBeforeUnmount, ref} from 'vue';
import {router, usePage} from '@inertiajs/vue3';
import {
    Select,
    SelectValue,
    SelectTrigger,
    SelectContent,
    SelectItem,
    SelectItemText,
} from '@/components/ui/select';


const props = defineProps({
    messages: Array,
    users: Array
});

const page = usePage();
const user = page.props.auth.user;
const users = page.props.users.data;
const messages = page.props.messages.data;

const content = ref('');
const selectedUser = ref();


onMounted(() => {
    window.Echo.private('chat.'+page.props.auth.user.id)
        .subscribed(() => { // Optional: callback when subscription is successful
            console.log('Subscribed to private channel');
            router.post('/chat-connect', { user: page.props.auth.user.id }, {
                onSuccess: () => console.log("User "+page.props.auth.user.name+" chat online")
            });
        })
        .listen('MessageSaved', (event) => {
            if(typeof messages !== 'undefined') {
                messages.push(event.message)
            }
        })
});

onBeforeUnmount(() => {
    router.post('/chat-disconnect', { user: page.props.auth.user.id }, {
        onSuccess: () => console.log("User "+page.props.auth.user.name+" chat offline")
    });
    let messagesId = messages.map( message => message.id);
    router.post('/messages-status-update', { messages: messagesId }, {
        onSuccess: () => console.log("Messages status updated")
    });
    console.log('Unsubscribed to private channel')
});

const userChange = (event) => {
    window.Echo.private('chat.'+ event)
        .subscribed(() => { // Optional: callback when subscription is successful
            console.log('Subscribed to private channel ' + event );
        })
        .listen('MessageSaved', (event) => {
            console.log(event.message);
            if(typeof messages !== 'undefined') {
                messages.push(event.message)
            }
        })
}

//console.log(messages)

const sendMessage = () => {
    console.log(selectedUser.value)
    if (!content.value) return;
    router.post('/chat', { content: content.value, user: selectedUser.value }, {
        onSuccess: () => content.value = ''
    });
}
</script>

<template>

    <div class="max-w-2xl mx-auto p-6">

        <div class="card flex justify-center">
            <Select v-model="selectedUser" @update:modelValue="userChange">
                <SelectTrigger class="w-[180px]">
                    <SelectValue placeholder="Select a user" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="user in users" :key="user.id" :value="user.id">
                        <SelectItemText>{{ user.name }}</SelectItemText>
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <h1 class="text-2xl font-bold mb-4">Chat Room</h1>
        <div class="border rounded p-4 h-80 overflow-y-auto mb-4 bg-gray-100">

            <div v-if="messages">
                <div v-for="msg in messages" :key="msg.id" class="mb-2">
                    <div v-for="sender in users" :key="sender.id">
                        <p v-if="sender.id == msg.user_id" style="text-align:right">
                            <strong>{{ sender.name }}:</strong>{{ msg.content }}
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <form @submit.prevent="sendMessage" class="flex">
            <input
                v-model="content"
                type="text"
                class="flex-1 border rounded-l px-3 py-2"
                placeholder="Type your message..."
            >
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r">
                Send
            </button>
        </form>
    </div>
</template>

<style scoped>

</style>
